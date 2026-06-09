<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id')->unique();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('car_id')->constrained('cars');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('pickup_location')->nullable();
            $table->boolean('use_driver')->default(false);
            $table->text('notes')->nullable();
            $table->integer('total_price');
            $table->string('status')->default('PENDING_PAYMENT');
            $table->dateTime('payment_deadline');
            $table->timestamps();

            $table->index(['car_id', 'start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
