<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);
            $table->string('action', 50);
            $table->string('slug', 120)->unique();
            $table->string('label', 150);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
