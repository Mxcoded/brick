<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('base_occupancy')->default(2)->after('capacity');
            $table->decimal('extra_adult_fee', 10, 2)->default(0)->after('base_occupancy');
            $table->decimal('extra_child_fee', 10, 2)->default(0)->after('extra_adult_fee');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['base_occupancy', 'extra_adult_fee', 'extra_child_fee']);
        });
    }
};
