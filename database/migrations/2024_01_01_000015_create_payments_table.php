<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('bank_sender_name', 150)->nullable();
            $table->string('bank_sender_account', 50)->nullable();
            $table->string('proof_path'); // storage privat
            $table->string('status', 20)->default('menunggu'); // menunggu, terverifikasi, ditolak
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
