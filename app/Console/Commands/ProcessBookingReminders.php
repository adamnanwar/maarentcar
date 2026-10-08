<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ProcessBookingReminders extends Command
{
    protected $signature = 'bookings:process-reminders';

    protected $description = 'Batalkan booking yang kedaluwarsa (belum dibayar dalam 1 jam) dan kirim notifikasi masa sewa (dimulai, akan berakhir, pengingat kembalikan mobil).';

    public function handle(): int
    {
        $expired = Booking::sweepOverduePayments();
        $this->info("{$expired} booking dibatalkan otomatis karena batas waktu pembayaran lewat.");

        $reminders = Booking::sweepRentalReminders();
        $this->info("{$reminders} notifikasi masa sewa terkirim.");

        return self::SUCCESS;
    }
}
