<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
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
            'type' => 'payment_success',
            'booking_id' => $this->booking->id,
            'order_id' => $this->booking->order_id,
            'message' => "Pembayaran untuk booking {$this->booking->order_id} berhasil!",
            'url' => route('bookings.show', $this->booking),
        ];
    }
}
