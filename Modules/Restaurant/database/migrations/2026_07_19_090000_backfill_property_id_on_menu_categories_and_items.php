<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $properties = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        if (empty($properties)) {
            return;
        }

        // Get all unique category names (from any row — null or not)
        $categoryNames = DB::table('restaurant_menu_categories')
            ->distinct()
            ->pluck('name')
            ->toArray();

        foreach ($properties as $propertyId) {
            foreach ($categoryNames as $categoryName) {
                $existing = DB::table('restaurant_menu_categories')
                    ->where('name', $categoryName)
                    ->where('property_id', $propertyId)
                    ->first();

                if ($existing) {
                    $newCategoryId = $existing->id;
                } else {
                    $newCategoryId = DB::table('restaurant_menu_categories')->insertGetId([
                        'name' => $categoryName,
                        'parent_id' => null,
                        'property_id' => $propertyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Get the original category for this name (any property) to find its items
                $originalCategory = DB::table('restaurant_menu_categories')
                    ->where('name', $categoryName)
                    ->first();

                if (! $originalCategory) {
                    continue;
                }

                $existingItemNames = DB::table('restaurant_menu_items')
                    ->where('property_id', $propertyId)
                    ->where('restaurant_menu_categories_id', $newCategoryId)
                    ->pluck('name')
                    ->toArray();

                $items = DB::table('restaurant_menu_items')
                    ->where('restaurant_menu_categories_id', $originalCategory->id)
                    ->get();

                foreach ($items as $item) {
                    if (in_array($item->name, $existingItemNames)) {
                        continue;
                    }

                    DB::table('restaurant_menu_items')->insert([
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'restaurant_menu_categories_id' => $newCategoryId,
                        'is_available' => $item->is_available,
                        'property_id' => $propertyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Remove all orphan rows (null property_id or duplicates)
        DB::table('restaurant_menu_items')->whereNull('property_id')->delete();
        DB::table('restaurant_menu_categories')->whereNull('property_id')->delete();
    }

    public function down(): void
    {
        DB::table('restaurant_menu_items')->whereNull('property_id')->delete();
        DB::table('restaurant_menu_categories')->whereNull('property_id')->delete();
    }
};
