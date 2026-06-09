<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Dibuat - Menunggu Pembayaran',
        );
    }

    public function content(): Content
    {
        $this->booking->load(['car', 'user']);

        return new Content(
            view: 'emails.booking-created',
            with: [
                'name' => $this->booking->user->name,
                'orderId' => $this->booking->order_id,
                'carName' => $this->booking->car->name,
                'startDate' => $this->booking->start_date->format('d M Y'),
                'endDate' => $this->booking->end_date->format('d M Y'),
                'totalPrice' => number_format($this->booking->total_price, 0, ',', '.'),
                'deadline' => $this->booking->payment_deadline->format('d M Y H:i'),
                'useDriver' => $this->booking->use_driver,
            ],
        );
    }
}
