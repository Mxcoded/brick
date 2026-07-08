<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('restaurant_order_items')->truncate();
        DB::table('restaurant_orders')->truncate();
        DB::table('restaurant_menu_items')->truncate();
        DB::table('restaurant_menu_categories')->truncate();
        DB::table('restaurant_tables')->truncate();
        DB::table('waiter_shifts')->truncate();
        DB::table('restaurant_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call(CategorySeeder::class);
        $this->call(MenuItemSeeder::class);
        $this->call(TableSeeder::class);
        $this->call(RestaurantSettingSeeder::class);
    }
}
