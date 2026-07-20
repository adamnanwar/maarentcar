<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('package_id')->nullable()->constrained('tour_packages');
            $table->smallInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestampsTz();

            $table->unique('booking_id');
            $table->index('vehicle_id');
            $table->index('package_id');
        });

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_review_target CHECK (vehicle_id IS NOT NULL OR package_id IS NOT NULL)');
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
