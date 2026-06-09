<?php

namespace App\Services;

use App\Jobs\SendBookingEmailJob;
use App\Models\Booking;
use App\Models\EmailLog;

class EmailService
{
    /**
     * Send booking-related email via queue.
     */
    public function sendBookingEmail(Booking $booking, string $emailType, array $additionalData = []): void
    {
        if (! $emailType) {
            return;
        }

        SendBookingEmailJob::dispatch($booking, $emailType, $additionalData);
    }

    /**
     * Log email sending result.
     */
    public function logEmail(
        ?Booking $booking,
        string $to,
        string $subject,
        string $status,
        ?string $errorMessage = null
    ): void {
        EmailLog::create([
            'booking_id' => $booking?->id,
            'to' => $to,
            'subject' => $subject,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }
}
