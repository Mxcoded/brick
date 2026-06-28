<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'room_types', 'room_units', 'registrations',
        'charge_types', 'rate_codes', 'booking_sources',
        'guest_types', 'channels', 'night_audits',
        'bookings', 'corporate_accounts',
    ];

    public function up(): void
    {
        $defaultProperty = Property::active()->orderBy('id')->first();
        if (! $defaultProperty) {
            return;
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'property_id')) {
                continue;
            }
            $count = DB::table($table)->whereNull('property_id')->count();
            if ($count > 0) {
                DB::table($table)->whereNull('property_id')->update(['property_id' => $defaultProperty->id]);
                // Cannot use $this->command in anonymous migrations
            }
        }
    }

    public function down(): void
    {
        // Irreversible — cannot determine which records were originally orphaned.
    }
};
