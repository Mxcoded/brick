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
        MenuItem::create(['restaurant_menu_categories_id' => 1, 'name' => 'Spring Rolls', 'description' => 'Crispy spring rolls with sweet chili dip.', 'price' => 1200.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 1, 'name' => 'Chicken Wings', 'description' => 'Spicy grilled chicken wings.', 'price' => 1800.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 1, 'name' => 'Samosas', 'description' => 'Beef samosas with mint chutney.', 'price' => 1000.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 1, 'name' => 'Prawn Crackers', 'description' => 'Light and crispy prawn crackers.', 'price' => 800.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 1, 'name' => 'Pepper Soup', 'description' => 'Spicy traditional pepper soup.', 'price' => 2500.00]);

        // Main Courses (category 2)
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'Jollof Rice & Chicken', 'description' => 'Classic Nigerian jollof rice with fried chicken.', 'price' => 3500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'Fried Rice & Turkey', 'description' => 'Fried rice with grilled turkey.', 'price' => 3800.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'Pounded Yam & Egusi', 'description' => 'Smooth pounded yam with melon seed soup.', 'price' => 4000.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'White Rice & Stew', 'description' => 'White rice with tomato stew and plantain.', 'price' => 2800.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'Yam & Sauce', 'description' => 'Boiled yam with garden egg sauce.', 'price' => 2200.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 2, 'name' => 'Grilled Fish', 'description' => 'Whole grilled tilapia with sides.', 'price' => 4500.00]);

        // Desserts (category 3)
        MenuItem::create(['restaurant_menu_categories_id' => 3, 'name' => 'Ice Cream', 'description' => 'Vanilla, chocolate, or strawberry scoop.', 'price' => 1500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 3, 'name' => 'Chocolate Cake', 'description' => 'Rich layered chocolate cake slice.', 'price' => 2000.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 3, 'name' => 'Fruit Salad', 'description' => 'Fresh seasonal fruit salad.', 'price' => 1800.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 3, 'name' => 'Cheesecake', 'description' => 'New York-style cheesecake.', 'price' => 2500.00]);

        // Beverages (category 4)
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Coke', 'description' => 'Chilled Coca-Cola 330ml.', 'price' => 500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Fanta', 'description' => 'Chilled Fanta Orange 330ml.', 'price' => 500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Sprite', 'description' => 'Chilled Sprite 330ml.', 'price' => 500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Malt', 'description' => 'Malta Guinness 330ml.', 'price' => 700.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Zobo', 'description' => 'Chilled hibiscus zobo drink.', 'price' => 400.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Chapman', 'description' => 'Classic Nigerian chapman mocktail.', 'price' => 1500.00]);
        MenuItem::create(['restaurant_menu_categories_id' => 4, 'name' => 'Water', 'description' => 'Natural spring water 500ml.', 'price' => 300.00]);
    }
}
