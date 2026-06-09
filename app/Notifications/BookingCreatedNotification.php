<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_created',
            'booking_id' => $this->booking->id,
            'order_id' => $this->booking->order_id,
            'message' => "Booking {$this->booking->order_id} berhasil dibuat. Menunggu verifikasi dokumen.",
            'url' => route('bookings.show', $this->booking),
        ];
    }
}
