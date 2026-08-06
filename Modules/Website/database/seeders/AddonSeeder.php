<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Addon;

class AddonSeeder extends Seeder
{
    /**
     * Seed default sellable add-ons / upsells.
     */
    public function run(): void
    {
        $addons = [
            [
                'name' => 'Breakfast for Two',
                'slug' => 'breakfast-for-two',
                'description' => 'Daily continental breakfast served in our restaurant or in-room.',
                'price' => 7500,
                'is_per_night' => true,
                'is_active' => true,
                'icon' => 'fas fa-coffee',
                'sort_order' => 1,
            ],
            [
                'name' => 'Airport Pickup',
                'slug' => 'airport-pickup',
                'description' => 'Chauffeured transfer from Nnamdi Azikiwe International Airport.',
                'price' => 20000,
                'is_per_night' => false,
                'is_active' => true,
                'icon' => 'fas fa-shuttle-van',
                'sort_order' => 2,
            ],
            [
                'name' => 'Late Check-out',
                'slug' => 'late-checkout',
                'description' => 'Enjoy your room until 3 PM on your departure day.',
                'price' => 12000,
                'is_per_night' => false,
                'is_active' => true,
                'icon' => 'fas fa-clock',
                'sort_order' => 3,
            ],
            [
                'name' => 'Spa Session',
                'slug' => 'spa-session',
                'description' => 'One relaxing 60-minute massage in our spa.',
                'price' => 18000,
                'is_per_night' => false,
                'is_active' => true,
                'icon' => 'fas fa-spa',
                'sort_order' => 4,
            ],
            [
                'name' => 'Welcome Fruit Platter',
                'slug' => 'welcome-fruit-platter',
                'description' => 'A fresh seasonal fruit platter waiting in your room.',
                'price' => 5000,
                'is_per_night' => false,
                'is_active' => true,
                'icon' => 'fas fa-apple-alt',
                'sort_order' => 5,
            ],
            [
                'name' => 'Dinner for Two',
                'slug' => 'dinner-for-two',
                'description' => 'A curated three-course dinner for two in our restaurant.',
                'price' => 35000,
                'is_per_night' => false,
                'is_active' => true,
                'icon' => 'fas fa-utensils',
                'sort_order' => 6,
            ],
        ];

        foreach ($addons as $addon) {
            Addon::firstOrCreate(
                ['slug' => $addon['slug']], // Check by slug to avoid duplicates
                $addon
            );
        }
    }
}
