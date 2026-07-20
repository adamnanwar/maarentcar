<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingStatusChangedNotification extends Notification
{
    public function __construct(protected Booking $booking, protected ?string $customMessage = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'booking_status',
            'message' => $this->message(),
            'url' => "/booking/{$this->booking->id}",
        ];
    }

    protected function message(): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        $code = $this->booking->booking_code;

        return match ($this->booking->status) {
            Booking::STATUS_MENUNGGU_PEMBAYARAN => "Booking {$code} dibuat. Silakan lakukan pembayaran.",
            Booking::STATUS_MENUNGGU_VERIFIKASI => "Bukti pembayaran untuk booking {$code} diterima, menunggu verifikasi kami.",
            Booking::STATUS_DIKONFIRMASI => "Booking {$code} telah dikonfirmasi. Sampai jumpa!",
            Booking::STATUS_BERLANGSUNG => "Booking {$code} sedang berlangsung.",
            Booking::STATUS_SELESAI => "Booking {$code} telah selesai. Jangan lupa beri ulasan!",
            Booking::STATUS_DITOLAK => "Bukti pembayaran untuk booking {$code} ditolak. Silakan unggah ulang.",
            Booking::STATUS_DIBATALKAN => "Booking {$code} telah dibatalkan.",
            default => "Status booking {$code} diperbarui.",
        };
    }
}
