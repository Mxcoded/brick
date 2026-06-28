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
        foreach (['A1', 'B2', 'C3'] as $number) {
            Table::updateOrCreate(
                ['number' => $number]
            );
        }
    }
}
