<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appetizersId = MenuCategory::where('name', 'Appetizers')->value('id');
        $mainCoursesId = MenuCategory::where('name', 'Main Courses')->value('id');

        if ($appetizersId) {
            MenuItem::updateOrCreate(
                ['name' => 'Spicy Jollof Rice'],
                [
                    'restaurant_menu_categories_id' => $appetizersId,
                    'description' => 'Delicious Nigerian-style rice with a spicy tomato base.',
                    'price' => 1500.00,
                ]
            );
        }

        if ($mainCoursesId) {
            MenuItem::updateOrCreate(
                ['name' => 'Grilled Chicken'],
                [
                    'restaurant_menu_categories_id' => $mainCoursesId,
                    'description' => 'Juicy grilled chicken served with side salad.',
                    'price' => 2000.00,
                ]
            );
        }
    }
}
