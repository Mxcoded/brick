<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\MenuCategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Appetizers', 'Main Courses', 'Desserts', 'Beverages'];

        foreach ($categories as $name) {
            MenuCategory::updateOrCreate(
                ['name' => $name],
                ['parent_id' => null]
            );
        }
    }
}
