<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingInProgressMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sewa Dimulai - Selamat Berkendara!',
        );
    }

    public function content(): Content
    {
        $this->booking->load(['car', 'user']);

        return new Content(
            view: 'emails.booking-in-progress',
            with: [
                'name' => $this->booking->user->name,
                'orderId' => $this->booking->order_id,
                'carName' => $this->booking->car->name,
                'startDate' => $this->booking->start_date->format('d M Y'),
                'endDate' => $this->booking->end_date->format('d M Y'),
            ],
        );
    }
}
