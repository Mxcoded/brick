<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\MenuCategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Appetizers', 'Main Courses', 'Desserts', 'Beverages'];
        $propertyIds = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        foreach ($propertyIds as $propertyId) {
            foreach ($categories as $name) {
                MenuCategory::updateOrCreate(
                    ['name' => $name, 'property_id' => $propertyId],
                    ['parent_id' => null]
                );
            }
        }
    }
}
