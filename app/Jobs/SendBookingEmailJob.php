<?php

namespace App\Jobs;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingCompletedMail;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingExpiredMail;
use App\Mail\BookingInProgressMail;
use App\Mail\PaymentPaidMail;
use App\Models\Booking;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Booking $booking,
        public string $emailType,
        public array $additionalData = []
    ) {}

    public function handle(EmailService $emailService): void
    {
        $this->booking->load(['user', 'car']);

        $mailable = $this->getMailable();
        if (! $mailable) {
            return;
        }

        $to = $this->booking->user->email;
        $subject = $mailable->envelope()->subject;

        try {
            Mail::to($to)->send($mailable);

            $emailService->logEmail(
                $this->booking,
                $to,
                $subject,
                'SENT'
            );
        } catch (\Exception $e) {
            $emailService->logEmail(
                $this->booking,
                $to,
                $subject,
                'FAILED',
                $e->getMessage()
            );

            throw $e;
        }
    }

    private function getMailable(): ?object
    {
        return match ($this->emailType) {
            'booking_created' => new BookingCreatedMail($this->booking),
            'payment_paid' => new PaymentPaidMail($this->booking),
            'booking_expired' => new BookingExpiredMail($this->booking),
            'booking_cancelled' => new BookingCancelledMail(
                $this->booking,
                $this->additionalData['reason'] ?? null
            ),
            'booking_in_progress' => new BookingInProgressMail($this->booking),
            'booking_completed' => new BookingCompletedMail($this->booking),
            default => null,
        };
    }
}
