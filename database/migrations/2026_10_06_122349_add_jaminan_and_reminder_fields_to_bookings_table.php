<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('ktp_photo_path')->nullable()->after('pickup_address_snapshot');
            $table->timestampTz('payment_due_at')->nullable()->after('status');
            $table->timestampTz('notified_start_at')->nullable()->after('payment_due_at');
            $table->timestampTz('notified_ending_soon_at')->nullable()->after('notified_start_at');
            $table->timestampTz('notified_return_reminder_at')->nullable()->after('notified_ending_soon_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'ktp_photo_path',
                'payment_due_at',
                'notified_start_at',
                'notified_ending_soon_at',
                'notified_return_reminder_at',
            ]);
        });
    }
};
