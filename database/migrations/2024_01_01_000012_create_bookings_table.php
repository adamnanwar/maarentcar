<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->foreignId('user_id')->constrained();

            $table->string('booking_type', 20); // mobil, paket_wisata
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('package_id')->nullable()->constrained('tour_packages');

            $table->timestampTz('start_datetime');
            $table->timestampTz('end_datetime');
            $table->smallInteger('duration_days');

            $table->boolean('with_driver')->default(false);
            $table->string('delivery_method', 30); // pickup_at_office, delivered_to_address, driver_pickup

            $table->jsonb('pickup_address_snapshot')->nullable();
            $table->smallInteger('passenger_count')->nullable();

            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('driver_fee', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('addon_total', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->string('status', 30)->default('menunggu_pembayaran');
            // menunggu_pembayaran, menunggu_verifikasi, dikonfirmasi, berlangsung, selesai, ditolak, dibatalkan

            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestampsTz();

            $table->index(['vehicle_id', 'start_datetime', 'end_datetime']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE bookings ADD CONSTRAINT chk_booking_type CHECK (
                (booking_type = 'mobil' AND vehicle_id IS NOT NULL AND package_id IS NULL)
                OR
                (booking_type = 'paket_wisata' AND package_id IS NOT NULL)
            )
        SQL);

        DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_dates CHECK (end_datetime > start_datetime)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
