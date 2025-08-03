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
    
        Table::create(['number' => 'A1']);
        Table::create(['number' => 'B2']);
        Table::create(['number' => 'C3']);
    }
}
