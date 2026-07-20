<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('vehicle_categories');
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->smallInteger('year')->nullable();
            $table->string('plate_number', 20)->unique(); // internal, tidak tampil publik
            $table->string('transmission', 20)->default('manual'); // manual, automatic
            $table->string('fuel_type', 20)->default('bensin'); // bensin, diesel, listrik
            $table->smallInteger('seat_capacity');
            $table->decimal('price_per_day', 12, 2);
            $table->decimal('driver_fee_per_day', 12, 2)->default(0);
            $table->decimal('base_delivery_fee', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('tersedia'); // tersedia, perawatan, nonaktif
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
