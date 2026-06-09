<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle Midtrans webhook notification
     */
    public function webhook(Request $request)
    {
        // Get notification data
        $notificationBody = $request->all();

        Log::info('Midtrans Webhook Received', $notificationBody);

        // Verify signature
        $orderId = $notificationBody['order_id'] ?? null;
        $statusCode = $notificationBody['status_code'] ?? null;
        $grossAmount = $notificationBody['gross_amount'] ?? null;
        $serverKey = config('midtrans.server_key');
        $signatureKey = $notificationBody['signature_key'] ?? null;

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans Webhook: Invalid signature', [
                'order_id' => $orderId,
                'expected' => $expectedSignature,
                'received' => $signatureKey,
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Find booking
        $booking = Booking::where('order_id', $orderId)->first();

        if (!$booking) {
            Log::warning('Midtrans Webhook: Booking not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Check if already processed (idempotency)
        if ($booking->status === Booking::STATUS_PAID) {
            Log::info('Midtrans Webhook: Payment already processed', ['order_id' => $orderId]);
            return response()->json(['message' => 'Already processed']);
        }

        // Process based on transaction status
        $transactionStatus = $notificationBody['transaction_status'] ?? null;
        $fraudStatus = $notificationBody['fraud_status'] ?? null;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $this->handlePaymentSuccess($booking, $notificationBody);
            } elseif ($fraudStatus === 'challenge') {
                // Payment needs review - keep status as pending payment
                Log::info('Midtrans Webhook: Payment challenged', ['order_id' => $orderId]);
            }
        } elseif ($transactionStatus === 'settlement') {
            $this->handlePaymentSuccess($booking, $notificationBody);
        } elseif ($transactionStatus === 'pending') {
            // Keep status as PENDING_PAYMENT
            Log::info('Midtrans Webhook: Payment pending', ['order_id' => $orderId]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
            ]);
            Log::info('Midtrans Webhook: Payment failed/expired', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
            ]);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess(Booking $booking, array $notificationBody): void
    {
        $booking->update([
            'status' => Booking::STATUS_PAID,
        ]);

        // Notify user
        $booking->load('user');
        $booking->user->notify(new PaymentSuccessNotification($booking));

        Log::info('Midtrans Webhook: Payment successful', [
            'order_id' => $booking->order_id,
            'amount' => $notificationBody['gross_amount'] ?? 0,
        ]);
    }
}
