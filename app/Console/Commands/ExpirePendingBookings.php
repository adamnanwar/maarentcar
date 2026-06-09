<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:expire-pending';

    /**
     * The console command description.
     */
    protected $description = 'Expire bookings that have passed their payment deadline';

    /**
     * Execute the console command.
     */
    public function handle(BookingService $bookingService): int
    {
        $this->info('Checking for expired bookings...');

        $count = $bookingService->expirePendingBookings();

        if ($count > 0) {
            $this->info("Expired {$count} booking(s).");
        } else {
            $this->info('No bookings to expire.');
        }

        return Command::SUCCESS;
    }
}
