<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('restaurant_menu_categories')->insert([
            ['name' => 'Appetizers', 'parent_id' => NULL,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Main Courses', 'parent_id' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desserts', 'parent_id' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beverages', 'parent_id' => NULL, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
