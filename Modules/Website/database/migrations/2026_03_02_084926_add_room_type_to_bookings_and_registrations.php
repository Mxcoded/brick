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
        // Update bookings table
        Schema::table('bookings', function (Blueprint $table) {
            // Add new columns (after room_id for logical ordering)
            $table->foreignId('room_type_id')->nullable()->after('room_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_unit_id')->nullable()->after('room_type_id')->constrained()->onDelete('set null');
            // Note: room_unit_id is nullable because unit is assigned at check-in, not booking time
        });

        // Update registrations table
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('room_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_unit_id')->nullable()->after('room_type_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropForeign(['room_unit_id']);
            $table->dropColumn(['room_type_id', 'room_unit_id']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropForeign(['room_unit_id']);
            $table->dropColumn(['room_type_id', 'room_unit_id']);
        });
    }
};
