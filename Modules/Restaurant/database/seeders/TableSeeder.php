<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Table;

class TableSeeder extends Seeder
{
    private array $tablesBySection = [
        'Window' => ['A1', 'A2', 'A3', 'A4'],
        'Center' => ['B1', 'B2', 'B3', 'B4'],
        'Patio' => ['C1', 'C2', 'C3'],
        'VIP' => ['VIP1', 'VIP2'],
    ];

    public function run(): void
    {
        $propertyIds = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        foreach ($propertyIds as $propertyId) {
            foreach ($this->tablesBySection as $section => $numbers) {
                foreach ($numbers as $number) {
                    Table::updateOrCreate(
                        ['number' => $number, 'property_id' => $propertyId],
                        ['section' => $section, 'capacity' => 4]
                    );
                }
            }
        }
    }
}
