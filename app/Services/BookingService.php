<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private PaymentService $paymentService,
        private EmailService $emailService
    ) {}

    /**
     * Check if a car is available for the given date range.
     */
    public function checkAvailability(string $carId, Carbon $startDate, Carbon $endDate): bool
    {
        // Find overlapping bookings with active statuses
        $overlapping = Booking::where('car_id', $carId)
            ->whereIn('status', ['PENDING_PAYMENT', 'PAID', 'IN_PROGRESS'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    // New booking starts during existing booking
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // New booking ends during existing booking
                    $q->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $endDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // New booking contains existing booking
                    $q->where('start_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                });
            })
            ->exists();

        return ! $overlapping;
    }

    /**
     * Create a new booking with payment.
     *
     * @return array{booking: Booking, snap_token: string}|array{error: string}
     */
    public function createBooking(User $user, array $data): array
    {
        $car = Car::findOrFail($data['car_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        // Check availability
        if (! $this->checkAvailability($car->id, $startDate, $endDate)) {
            return ['error' => 'Mobil tidak tersedia untuk tanggal yang dipilih.'];
        }

        // Calculate total price
        $days = $startDate->diffInDays($endDate) + 1; // Include both start and end date
        $pricePerDay = $data['use_driver']
            ? ($car->with_driver_price_per_day ?? $car->price_per_day)
            : $car->price_per_day;
        $totalPrice = $days * $pricePerDay;

        // Generate unique order ID
        $orderId = $this->generateOrderId();

        // Calculate payment deadline
        $paymentDeadline = now()->addMinutes(
            config('midtrans.payment_deadline_minutes', 60)
        );

        // Create booking
        $booking = Booking::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'car_id' => $car->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pickup_location' => $data['pickup_location'] ?? null,
            'use_driver' => $data['use_driver'],
            'notes' => $data['notes'] ?? null,
            'total_price' => $totalPrice,
            'status' => 'PENDING_PAYMENT',
            'payment_deadline' => $paymentDeadline,
        ]);

        // Create payment record
        Payment::create([
            'booking_id' => $booking->id,
            'order_id' => $orderId,
            'status' => 'PENDING',
            'gross_amount' => $totalPrice,
        ]);

        // Get Snap token
        try {
            $snapToken = $this->paymentService->createSnapToken($booking);
        } catch (\Exception $e) {
            // Rollback: delete booking and payment
            $booking->payment()->delete();
            $booking->delete();

            return ['error' => 'Gagal membuat transaksi pembayaran. Silakan coba lagi.'];
        }

        // Send booking created email
        $this->emailService->sendBookingEmail($booking, 'booking_created');

        return [
            'booking' => $booking->load(['car', 'payment']),
            'snap_token' => $snapToken,
        ];
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        if ($booking->status !== 'PENDING_PAYMENT') {
            throw new \Exception('Hanya booking dengan status PENDING_PAYMENT yang dapat dibatalkan.');
        }

        $booking->update([
            'status' => 'CANCELLED',
        ]);

        // Update payment status
        if ($booking->payment) {
            $booking->payment->update(['status' => 'CANCEL']);
        }

        // Send cancellation email
        $this->emailService->sendBookingEmail($booking, 'booking_cancelled', [
            'reason' => $reason,
        ]);

        return $booking->fresh(['car', 'payment']);
    }

    /**
     * Expire pending bookings that have passed their payment deadline.
     */
    public function expirePendingBookings(): int
    {
        $expiredBookings = Booking::where('status', 'PENDING_PAYMENT')
            ->where('payment_deadline', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'EXPIRED']);

            if ($booking->payment) {
                $booking->payment->update(['status' => 'EXPIRE']);
            }

            // Send expiration email
            $this->emailService->sendBookingEmail($booking, 'booking_expired');
            $count++;
        }

        return $count;
    }

    /**
     * Update booking status (admin operation).
     */
    public function updateStatus(Booking $booking, string $newStatus, ?string $note = null): Booking
    {
        // Validate status transition
        $allowedTransitions = [
            'PAID' => ['IN_PROGRESS', 'CANCELLED'],
            'IN_PROGRESS' => ['COMPLETED', 'CANCELLED'],
        ];

        $currentStatus = $booking->status;
        $allowed = $allowedTransitions[$currentStatus] ?? [];

        if (! in_array($newStatus, $allowed)) {
            throw new \Exception("Tidak dapat mengubah status dari {$currentStatus} ke {$newStatus}.");
        }

        $booking->update(['status' => $newStatus]);

        // Send appropriate email
        $emailType = match ($newStatus) {
            'IN_PROGRESS' => 'booking_in_progress',
            'COMPLETED' => 'booking_completed',
            'CANCELLED' => 'booking_cancelled',
            default => null,
        };

        if ($emailType) {
            $this->emailService->sendBookingEmail($booking, $emailType, [
                'note' => $note,
            ]);
        }

        return $booking->fresh(['car', 'payment', 'user']);
    }

    /**
     * Generate a unique order ID.
     */
    private function generateOrderId(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$timestamp}-{$random}";
    }
}
