<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'room_types' => ['module' => false, 'after' => 'id'],
        'room_units' => ['module' => false, 'after' => 'room_type_id'],
        'registrations' => ['module' => false, 'after' => 'id'],
        'charge_types' => ['module' => false, 'after' => 'id'],
        'rate_codes' => ['module' => false, 'after' => 'id'],
        'booking_sources' => ['module' => false, 'after' => 'id'],
        'guest_types' => ['module' => false, 'after' => 'id'],
        'channels' => ['module' => false, 'after' => 'id'],
        'night_audits' => ['module' => false, 'after' => 'audit_date'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $config) {
            if (! Schema::hasColumn($table, 'property_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $col = $blueprint->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $config) {
            if (Schema::hasColumn($table, 'property_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['property_id']);
                    $blueprint->dropColumn('property_id');
                });
            }
        }
        // Also handle tables added by separate migration
        if (Schema::hasColumn('bookings', 'property_id')) {
            Schema::table('bookings', function (Blueprint $blueprint) {
                $blueprint->dropForeign(['property_id']);
                $blueprint->dropColumn('property_id');
            });
        }
    }
};
