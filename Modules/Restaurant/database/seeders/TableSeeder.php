<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\Table;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Section A — window-side
        Table::updateOrCreate(['number' => 'A1']);
        Table::updateOrCreate(['number' => 'A2']);
        Table::updateOrCreate(['number' => 'A3']);
        Table::updateOrCreate(['number' => 'A4']);

        // Section B — center
        Table::updateOrCreate(['number' => 'B1']);
        Table::updateOrCreate(['number' => 'B2']);
        Table::updateOrCreate(['number' => 'B3']);
        Table::updateOrCreate(['number' => 'B4']);

        // Section C — patio
        Table::updateOrCreate(['number' => 'C1']);
        Table::updateOrCreate(['number' => 'C2']);
        Table::updateOrCreate(['number' => 'C3']);

        // VIP section
        Table::updateOrCreate(['number' => 'VIP1']);
        Table::updateOrCreate(['number' => 'VIP2']);
    }
}
