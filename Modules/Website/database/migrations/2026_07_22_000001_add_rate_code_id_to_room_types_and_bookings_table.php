<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->foreignId('rate_code_id')->nullable()->after('price')->constrained('rate_codes')->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('rate_code_id')->nullable()->after('room_type_id')->constrained('rate_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropForeign(['rate_code_id']);
            $table->dropColumn('rate_code_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['rate_code_id']);
            $table->dropColumn('rate_code_id');
        });
    }
};
