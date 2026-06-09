<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public bool $approved
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->approved) {
            return [
                'type' => 'booking_approved',
                'booking_id' => $this->booking->id,
                'order_id' => $this->booking->order_id,
                'message' => "Dokumen booking {$this->booking->order_id} telah diverifikasi. Silakan lakukan pembayaran.",
                'url' => route('bookings.show', $this->booking),
            ];
        }

        return [
            'type' => 'booking_rejected',
            'booking_id' => $this->booking->id,
            'order_id' => $this->booking->order_id,
            'message' => "Dokumen booking {$this->booking->order_id} ditolak. Alasan: {$this->booking->admin_note}",
            'url' => route('bookings.show', $this->booking),
        ];
    }
}
