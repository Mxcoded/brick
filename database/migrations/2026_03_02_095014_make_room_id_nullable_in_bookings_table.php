<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make room_id nullable since we're transitioning to room_type_id.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Make room_id nullable (legacy field, replaced by room_type_id)
            $table->unsignedBigInteger('room_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert: Note - this may fail if there are NULL values
            $table->unsignedBigInteger('room_id')->nullable(false)->change();
        });
    }
};
