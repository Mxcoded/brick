<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an optional per-room-type sell limit (overbooking allowance).
     *
     * NULL means the room type sells its physical unit count (no overbooking).
     * A value above the unit count allows overbooking; a value below it is a
     * safety cap that stop-sells earlier in the inventory calendar.
     */
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('sell_limit')->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('sell_limit');
        });
    }
};
