<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingRentalReminderNotification extends Notification
{
    const REMINDER_START = 'rental_starting';
    const REMINDER_ENDING_SOON = 'rental_ending_soon';
    const REMINDER_RETURN = 'rental_return_reminder';

    public function __construct(protected Booking $booking, protected string $reminderType)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->reminderType,
            'message' => $this->message(),
            'url' => "/booking/{$this->booking->id}",
        ];
    }

    protected function message(): string
    {
        $code = $this->booking->booking_code;
        $item = $this->booking->vehicle?->name ?? $this->booking->package?->name ?? 'pesanan Anda';

        return match ($this->reminderType) {
            self::REMINDER_START => "Masa sewa {$item} ({$code}) dimulai hari ini. Selamat menikmati perjalanan Anda!",
            self::REMINDER_ENDING_SOON => "Masa sewa {$item} ({$code}) akan segera berakhir. Mohon siapkan pengembalian mobil tepat waktu.",
            self::REMINDER_RETURN => "Masa sewa {$item} ({$code}) sudah berakhir. Segera kembalikan mobil ke kantor We Rent Car apabila belum dikembalikan.",
            default => "Pembaruan untuk pesanan {$code}.",
        };
    }
}
