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
        // Safely rollback bookings table
        Schema::table('bookings', function (Blueprint $table) {
            // Use try-catch to safely ignore if the foreign key is already gone
            try {
                $table->dropForeign(['room_type_id']);
            } catch (Exception $e) {
            }

            try {
                $table->dropForeign(['room_unit_id']);
            } catch (Exception $e) {
            }

            // Check if column exists before dropping to prevent column missing errors
            if (Schema::hasColumn('bookings', 'room_type_id')) {
                $table->dropColumn('room_type_id');
            }
            if (Schema::hasColumn('bookings', 'room_unit_id')) {
                $table->dropColumn('room_unit_id');
            }
        });

        // Safely rollback registrations table (if it exists)
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                try {
                    $table->dropForeign(['room_type_id']);
                } catch (Exception $e) {
                }

                try {
                    $table->dropForeign(['room_unit_id']);
                } catch (Exception $e) {
                }

                if (Schema::hasColumn('registrations', 'room_type_id')) {
                    $table->dropColumn('room_type_id');
                }
                if (Schema::hasColumn('registrations', 'room_unit_id')) {
                    $table->dropColumn('room_unit_id');
                }
            });
        }
    }
};
