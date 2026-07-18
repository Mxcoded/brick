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
        // Appetizers (category 1)
        MenuItem::updateOrCreate(['name' => 'Spring Rolls'], ['restaurant_menu_categories_id' => 1, 'description' => 'Crispy spring rolls with sweet chili dip.', 'price' => 1200.00]);
        MenuItem::updateOrCreate(['name' => 'Chicken Wings'], ['restaurant_menu_categories_id' => 1, 'description' => 'Spicy grilled chicken wings.', 'price' => 1800.00]);
        MenuItem::updateOrCreate(['name' => 'Samosas'], ['restaurant_menu_categories_id' => 1, 'description' => 'Beef samosas with mint chutney.', 'price' => 1000.00]);
        MenuItem::updateOrCreate(['name' => 'Prawn Crackers'], ['restaurant_menu_categories_id' => 1, 'description' => 'Light and crispy prawn crackers.', 'price' => 800.00]);
        MenuItem::updateOrCreate(['name' => 'Pepper Soup'], ['restaurant_menu_categories_id' => 1, 'description' => 'Spicy traditional pepper soup.', 'price' => 2500.00]);

        // Main Courses (category 2)
        MenuItem::updateOrCreate(['name' => 'Jollof Rice & Chicken'], ['restaurant_menu_categories_id' => 2, 'description' => 'Classic Nigerian jollof rice with fried chicken.', 'price' => 3500.00]);
        MenuItem::updateOrCreate(['name' => 'Fried Rice & Turkey'], ['restaurant_menu_categories_id' => 2, 'description' => 'Fried rice with grilled turkey.', 'price' => 3800.00]);
        MenuItem::updateOrCreate(['name' => 'Pounded Yam & Egusi'], ['restaurant_menu_categories_id' => 2, 'description' => 'Smooth pounded yam with melon seed soup.', 'price' => 4000.00]);
        MenuItem::updateOrCreate(['name' => 'White Rice & Stew'], ['restaurant_menu_categories_id' => 2, 'description' => 'White rice with tomato stew and plantain.', 'price' => 2800.00]);
        MenuItem::updateOrCreate(['name' => 'Yam & Sauce'], ['restaurant_menu_categories_id' => 2, 'description' => 'Boiled yam with garden egg sauce.', 'price' => 2200.00]);
        MenuItem::updateOrCreate(['name' => 'Grilled Fish'], ['restaurant_menu_categories_id' => 2, 'description' => 'Whole grilled tilapia with sides.', 'price' => 4500.00]);

        // Desserts (category 3)
        MenuItem::updateOrCreate(['name' => 'Ice Cream'], ['restaurant_menu_categories_id' => 3, 'description' => 'Vanilla, chocolate, or strawberry scoop.', 'price' => 1500.00]);
        MenuItem::updateOrCreate(['name' => 'Chocolate Cake'], ['restaurant_menu_categories_id' => 3, 'description' => 'Rich layered chocolate cake slice.', 'price' => 2000.00]);
        MenuItem::updateOrCreate(['name' => 'Fruit Salad'], ['restaurant_menu_categories_id' => 3, 'description' => 'Fresh seasonal fruit salad.', 'price' => 1800.00]);
        MenuItem::updateOrCreate(['name' => 'Cheesecake'], ['restaurant_menu_categories_id' => 3, 'description' => 'New York-style cheesecake.', 'price' => 2500.00]);

        // Beverages (category 4)
        MenuItem::updateOrCreate(['name' => 'Coke'], ['restaurant_menu_categories_id' => 4, 'description' => 'Chilled Coca-Cola 330ml.', 'price' => 500.00]);
        MenuItem::updateOrCreate(['name' => 'Fanta'], ['restaurant_menu_categories_id' => 4, 'description' => 'Chilled Fanta Orange 330ml.', 'price' => 500.00]);
        MenuItem::updateOrCreate(['name' => 'Sprite'], ['restaurant_menu_categories_id' => 4, 'description' => 'Chilled Sprite 330ml.', 'price' => 500.00]);
        MenuItem::updateOrCreate(['name' => 'Malt'], ['restaurant_menu_categories_id' => 4, 'description' => 'Malta Guinness 330ml.', 'price' => 700.00]);
        MenuItem::updateOrCreate(['name' => 'Zobo'], ['restaurant_menu_categories_id' => 4, 'description' => 'Chilled hibiscus zobo drink.', 'price' => 400.00]);
        MenuItem::updateOrCreate(['name' => 'Chapman'], ['restaurant_menu_categories_id' => 4, 'description' => 'Classic Nigerian chapman mocktail.', 'price' => 1500.00]);
        MenuItem::updateOrCreate(['name' => 'Water'], ['restaurant_menu_categories_id' => 4, 'description' => 'Natural spring water 500ml.', 'price' => 300.00]);
    }
}
