<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'registrations',
            'rate_codes',
            'charge_types',
            'booking_sources',
            'guest_types',
            'channels',
            'night_audits',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->index('property_id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'registrations',
            'rate_codes',
            'charge_types',
            'booking_sources',
            'guest_types',
            'channels',
            'night_audits',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['property_id']);
            });
        }
    }
};
