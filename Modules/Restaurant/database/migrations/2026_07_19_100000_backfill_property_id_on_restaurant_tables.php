<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $properties = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        if (empty($properties)) {
            return;
        }

        $tableNumbers = DB::table('restaurant_tables')
            ->distinct()
            ->pluck('number')
            ->toArray();

        foreach ($properties as $propertyId) {
            foreach ($tableNumbers as $number) {
                $existing = DB::table('restaurant_tables')
                    ->where('number', $number)
                    ->where('property_id', $propertyId)
                    ->first();

                if ($existing) {
                    continue;
                }

                $original = DB::table('restaurant_tables')
                    ->where('number', $number)
                    ->first();

                if (! $original) {
                    continue;
                }

                DB::table('restaurant_tables')->insert([
                    'number' => $original->number,
                    'capacity' => $original->capacity,
                    'section' => $original->section,
                    'property_id' => $propertyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('restaurant_tables')->whereNull('property_id')->delete();
    }

    public function down(): void
    {
        DB::table('restaurant_tables')->whereNull('property_id')->delete();
    }
};
