<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make room_id nullable since new bookings use room_type_id/room_unit_id instead.
     * Legacy bookings still have room_id, but new ones won't need it.
     */
    public function up(): void
    {
        // Check if foreign key exists before trying to drop it
        $foreignKeyExists = $this->foreignKeyExists('bookings', 'bookings_room_id_foreign');

        if ($foreignKeyExists) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        }

        // Make room_id nullable
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable()->change();
        });

        // Re-add foreign key with nullable support (only if rooms table exists)
        if (Schema::hasTable('rooms')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Note: This will keep room_id nullable since we can't safely revert
     * without potentially breaking data integrity.
     */
    public function down(): void
    {
        // We don't revert the nullable change because:
        // 1. New bookings use room_type_id instead of room_id
        // 2. Existing data may have NULL room_id values
        // 3. Forcing NOT NULL would break the application

        // Just ensure foreign key exists if it was removed
        $foreignKeyExists = $this->foreignKeyExists('bookings', 'bookings_room_id_foreign');

        if (! $foreignKeyExists && Schema::hasTable('rooms') && Schema::hasColumn('bookings', 'room_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            });
        }
    }

    /**
     * Check if a foreign key exists on a table.
     */
    private function foreignKeyExists(string $table, string $foreignKey, string $column = 'room_id'): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $database = config('database.connections.mysql.database');

            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ? 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$database, $table, $foreignKey]);

            return $result[0]->count > 0;
        }

        // SQLite: check via PRAGMA
        $keys = DB::select("PRAGMA foreign_key_list($table)");

        return collect($keys)->pluck('from')->contains($column);
    }
};
