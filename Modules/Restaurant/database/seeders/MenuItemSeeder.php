<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MenuItem::create([
            'restaurant_menu_categories_id' => 1, // Make sure this ID exists in MenuCategory table
            'name' => 'Spicy Jollof Rice',
            'description' => 'Delicious Nigerian-style rice with a spicy tomato base.',
            'price' => 1500.00
        ]);

        MenuItem::create([
            'restaurant_menu_categories_id' => 2,
            'name' => 'Grilled Chicken',
            'description' => 'Juicy grilled chicken served with side salad.',
            'price' => 2000.00
        ]);
    }
}
