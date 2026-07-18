<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    private array $menuByCategory = [
        'Appetizers' => [
            ['name' => 'Spring Rolls', 'description' => 'Crispy spring rolls with sweet chili dip.', 'price' => 1200.00],
            ['name' => 'Chicken Wings', 'description' => 'Spicy grilled chicken wings.', 'price' => 1800.00],
            ['name' => 'Samosas', 'description' => 'Beef samosas with mint chutney.', 'price' => 1000.00],
            ['name' => 'Prawn Crackers', 'description' => 'Light and crispy prawn crackers.', 'price' => 800.00],
            ['name' => 'Pepper Soup', 'description' => 'Spicy traditional pepper soup.', 'price' => 2500.00],
        ],
        'Main Courses' => [
            ['name' => 'Jollof Rice & Chicken', 'description' => 'Classic Nigerian jollof rice with fried chicken.', 'price' => 3500.00],
            ['name' => 'Fried Rice & Turkey', 'description' => 'Fried rice with grilled turkey.', 'price' => 3800.00],
            ['name' => 'Pounded Yam & Egusi', 'description' => 'Smooth pounded yam with melon seed soup.', 'price' => 4000.00],
            ['name' => 'White Rice & Stew', 'description' => 'White rice with tomato stew and plantain.', 'price' => 2800.00],
            ['name' => 'Yam & Sauce', 'description' => 'Boiled yam with garden egg sauce.', 'price' => 2200.00],
            ['name' => 'Grilled Fish', 'description' => 'Whole grilled tilapia with sides.', 'price' => 4500.00],
        ],
        'Desserts' => [
            ['name' => 'Ice Cream', 'description' => 'Vanilla, chocolate, or strawberry scoop.', 'price' => 1500.00],
            ['name' => 'Chocolate Cake', 'description' => 'Rich layered chocolate cake slice.', 'price' => 2000.00],
            ['name' => 'Fruit Salad', 'description' => 'Fresh seasonal fruit salad.', 'price' => 1800.00],
            ['name' => 'Cheesecake', 'description' => 'New York-style cheesecake.', 'price' => 2500.00],
        ],
        'Beverages' => [
            ['name' => 'Coke', 'description' => 'Chilled Coca-Cola 330ml.', 'price' => 500.00],
            ['name' => 'Fanta', 'description' => 'Chilled Fanta Orange 330ml.', 'price' => 500.00],
            ['name' => 'Sprite', 'description' => 'Chilled Sprite 330ml.', 'price' => 500.00],
            ['name' => 'Malt', 'description' => 'Malta Guinness 330ml.', 'price' => 700.00],
            ['name' => 'Zobo', 'description' => 'Chilled hibiscus zobo drink.', 'price' => 400.00],
            ['name' => 'Chapman', 'description' => 'Classic Nigerian chapman mocktail.', 'price' => 1500.00],
            ['name' => 'Water', 'description' => 'Natural spring water 500ml.', 'price' => 300.00],
        ],
    ];

    public function run(): void
    {
        $propertyIds = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        foreach ($propertyIds as $propertyId) {
            $categoryIds = DB::table('restaurant_menu_categories')
                ->where('property_id', $propertyId)
                ->pluck('id', 'name');

            foreach ($this->menuByCategory as $categoryName => $items) {
                $categoryId = $categoryIds->get($categoryName);
                if (! $categoryId) {
                    continue;
                }

                foreach ($items as $item) {
                    MenuItem::updateOrCreate(
                        ['name' => $item['name'], 'property_id' => $propertyId],
                        [
                            'restaurant_menu_categories_id' => $categoryId,
                            'description' => $item['description'],
                            'price' => $item['price'],
                        ]
                    );
                }
            }
        }
    }
}
