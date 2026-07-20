<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_destinations', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->smallInteger('sort_order')->default(0);
            $table->primary(['package_id', 'destination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_destinations');
    }
};
