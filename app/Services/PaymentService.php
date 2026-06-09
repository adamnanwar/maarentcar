<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Midtrans Snap token for a booking.
     */
    public function createSnapToken(Booking $booking): string
    {
        $booking->load(['user', 'car']);

        $params = [
            'transaction_details' => [
                'order_id' => $booking->order_id,
                'gross_amount' => $booking->total_price,
            ],
            'customer_details' => [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $booking->car->id,
                    'price' => $booking->total_price,
                    'quantity' => 1,
                    'name' => $this->truncateName($booking->car->name.' - Rental'),
                ],
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => config('midtrans.payment_deadline_minutes', 60),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap token creation failed', [
                'order_id' => $booking->order_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle Midtrans webhook notification (idempotent).
     */
    public function handleWebhook(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;
        $transactionId = $notification['transaction_id'] ?? null;
        $paymentType = $notification['payment_type'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? 0;

        if (! $orderId) {
            Log::warning('Midtrans webhook: missing order_id', $notification);

            return;
        }

        // Verify signature
        if (! $this->verifySignature($notification)) {
            Log::warning('Midtrans webhook: invalid signature', ['order_id' => $orderId]);

            return;
        }

        // Find payment by order_id
        $payment = Payment::where('order_id', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans webhook: payment not found', ['order_id' => $orderId]);

            return;
        }

        // Map Midtrans status to internal status
        $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        // Idempotency check: skip if already processed with same or higher priority status
        if ($this->isStatusAlreadyProcessed($payment->status, $newStatus)) {
            Log::info('Midtrans webhook: status already processed', [
                'order_id' => $orderId,
                'current_status' => $payment->status,
                'new_status' => $newStatus,
            ]);

            return;
        }

        // Update payment
        $payment->update([
            'status' => $newStatus,
            'method' => $paymentType,
            'midtrans_transaction_id' => $transactionId,
            'raw_notification' => $notification,
        ]);

        // Update booking status accordingly
        $booking = $payment->booking;
        if ($booking) {
            $bookingStatus = $this->mapPaymentStatusToBookingStatus($newStatus, $booking->status);
            if ($bookingStatus && $bookingStatus !== $booking->status) {
                $booking->update(['status' => $bookingStatus]);

                // Dispatch email notification
                app(EmailService::class)->sendBookingEmail($booking, $this->getEmailTypeForStatus($bookingStatus));
            }
        }

        Log::info('Midtrans webhook: payment updated', [
            'order_id' => $orderId,
            'status' => $newStatus,
        ]);
    }

    /**
     * Verify Midtrans signature.
     */
    private function verifySignature(array $notification): bool
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return $signatureKey === $expectedSignature;
    }

    /**
     * Map Midtrans transaction status to internal payment status.
     */
    private function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                return 'PAID';
            } elseif ($fraudStatus === 'challenge') {
                return 'PENDING';
            }

            return 'DENY';
        }

        return match ($transactionStatus) {
            'settlement' => 'PAID',
            'pending' => 'PENDING',
            'deny' => 'DENY',
            'cancel' => 'CANCEL',
            'expire' => 'EXPIRE',
            'refund', 'partial_refund' => 'REFUND',
            default => 'PENDING',
        };
    }

    /**
     * Check if status is already processed (idempotency).
     */
    private function isStatusAlreadyProcessed(string $currentStatus, string $newStatus): bool
    {
        $statusPriority = [
            'PENDING' => 1,
            'DENY' => 2,
            'CANCEL' => 2,
            'EXPIRE' => 2,
            'PAID' => 3,
            'REFUND' => 4,
        ];

        $currentPriority = $statusPriority[$currentStatus] ?? 0;
        $newPriority = $statusPriority[$newStatus] ?? 0;

        // Skip if current status has higher or equal priority (except for same status)
        return $currentPriority >= $newPriority && $currentStatus !== 'PENDING';
    }

    /**
     * Map payment status to booking status.
     */
    private function mapPaymentStatusToBookingStatus(string $paymentStatus, string $currentBookingStatus): ?string
    {
        // Only update if booking is still in PENDING_PAYMENT
        if ($currentBookingStatus !== 'PENDING_PAYMENT') {
            return null;
        }

        return match ($paymentStatus) {
            'PAID' => 'PAID',
            'DENY', 'CANCEL' => 'CANCELLED',
            'EXPIRE' => 'EXPIRED',
            default => null,
        };
    }

    /**
     * Get email type for booking status.
     */
    private function getEmailTypeForStatus(string $status): ?string
    {
        return match ($status) {
            'PAID' => 'payment_paid',
            'CANCELLED' => 'booking_cancelled',
            'EXPIRED' => 'booking_expired',
            default => null,
        };
    }

    /**
     * Truncate item name to max 50 chars (Midtrans requirement).
     */
    private function truncateName(string $name): string
    {
        return mb_strlen($name) > 50 ? mb_substr($name, 0, 47).'...' : $name;
    }
}
