<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class AdminNewBookingNotification extends Notification
{
    public function __construct(protected Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenis = $this->booking->booking_type === Booking::TYPE_PAKET_WISATA ? 'paket wisata' : 'sewa mobil';

        return [
            'type' => 'admin_new_booking',
            'message' => "Booking {$jenis} baru {$this->booking->booking_code} dari {$this->booking->user->name} menunggu pembayaran.",
            'url' => "/admin/booking/{$this->booking->id}",
        ];
    }
}
