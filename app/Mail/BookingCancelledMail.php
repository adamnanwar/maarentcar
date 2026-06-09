<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Dibatalkan',
        );
    }

    public function content(): Content
    {
        $this->booking->load(['car', 'user']);

        return new Content(
            view: 'emails.booking-cancelled',
            with: [
                'name' => $this->booking->user->name,
                'orderId' => $this->booking->order_id,
                'carName' => $this->booking->car->name,
                'reason' => $this->reason,
            ],
        );
    }
}
