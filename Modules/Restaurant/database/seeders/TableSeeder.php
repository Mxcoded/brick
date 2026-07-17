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
        Table::create(['number' => 'A1']);
        Table::create(['number' => 'A2']);
        Table::create(['number' => 'A3']);
        Table::create(['number' => 'A4']);

        // Section B — center
        Table::create(['number' => 'B1']);
        Table::create(['number' => 'B2']);
        Table::create(['number' => 'B3']);
        Table::create(['number' => 'B4']);

        // Section C — patio
        Table::create(['number' => 'C1']);
        Table::create(['number' => 'C2']);
        Table::create(['number' => 'C3']);

        // VIP section
        Table::create(['number' => 'VIP1']);
        Table::create(['number' => 'VIP2']);
    }
}
