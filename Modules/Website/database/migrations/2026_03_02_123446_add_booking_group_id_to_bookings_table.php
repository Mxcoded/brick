<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'booking_group_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Group ID to link multiple bookings made in a single transaction
                $table->string('booking_group_id', 20)->nullable()->after('booking_reference')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'booking_group_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('booking_group_id');
            });
        }
    }
};
