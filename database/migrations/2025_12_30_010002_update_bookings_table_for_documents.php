<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add tour package relation
            $table->foreignUuid('tour_package_id')->nullable()->after('car_id')->constrained('tour_packages')->nullOnDelete();

            // Document uploads
            $table->string('ktp_image_url')->nullable()->after('notes');
            $table->string('sim_image_url')->nullable()->after('ktp_image_url');

            // Admin validation
            $table->text('admin_note')->nullable()->after('sim_image_url');

            // Snap token for payment
            $table->string('snap_token')->nullable()->after('status');

            // Change default status - using Laravel's change() for cross-database compatibility
            $table->string('status')->default('PENDING_VERIFICATION')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_package_id']);
            $table->dropColumn([
                'tour_package_id',
                'ktp_image_url',
                'sim_image_url',
                'admin_note',
                'snap_token',
            ]);

            // Revert default status
            $table->string('status')->default('PENDING_PAYMENT')->change();
        });
    }
};
