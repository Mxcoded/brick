<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Table;

class KdsDisplaySeeder extends Seeder
{
    public function run(): void
    {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'KDS Test Items'],
        );

        $jollof = MenuItem::firstOrCreate(
            ['name' => 'Jollof Rice'],
            [
                'restaurant_menu_categories_id' => $category->id,
                'price' => 2500.00,
                'is_available' => true,
            ]
        );

        $chicken = MenuItem::firstOrCreate(
            ['name' => 'Grilled Chicken'],
            [
                'restaurant_menu_categories_id' => $category->id,
                'price' => 3500.00,
                'is_available' => true,
            ]
        );

        $plantain = MenuItem::firstOrCreate(
            ['name' => 'Fried Plantain'],
            [
                'restaurant_menu_categories_id' => $category->id,
                'price' => 1200.00,
                'is_available' => true,
            ]
        );

        $table1 = Table::firstOrCreate(['number' => 'KDS-T1']);
        $table2 = Table::firstOrCreate(['number' => 'KDS-T2']);
        $table3 = Table::firstOrCreate(['number' => 'KDS-T3']);

        // Order 1: Pending (unaccepted) — should show in "Unaccepted" section
        $pendingOrder = Order::create([
            'type' => 'table',
            'source_id' => $table1->id,
            'status' => 'pending',
            'tracking_status' => null,
            'subtotal' => 3700.00,
            'vat' => 277.50,
            'vat_rate' => 7.5,
            'grand_total' => 3977.50,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $pendingOrder->id,
            'restaurant_menu_item_id' => $jollof->id,
            'quantity' => 1,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $pendingOrder->id,
            'restaurant_menu_item_id' => $plantain->id,
            'quantity' => 1,
        ]);

        // Order 2: Accepted / Preparing — should show in "Preparing" section
        $preparingOrder = Order::create([
            'type' => 'table',
            'source_id' => $table2->id,
            'status' => 'accepted',
            'tracking_status' => 'preparing',
            'subtotal' => 6000.00,
            'vat' => 450.00,
            'vat_rate' => 7.5,
            'grand_total' => 6450.00,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $preparingOrder->id,
            'restaurant_menu_item_id' => $jollof->id,
            'quantity' => 1,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $preparingOrder->id,
            'restaurant_menu_item_id' => $chicken->id,
            'quantity' => 1,
        ]);

        // Order 3: Ready to serve — should show in "Ready to Serve" section
        $readyOrder = Order::create([
            'type' => 'table',
            'source_id' => $table3->id,
            'status' => 'accepted',
            'tracking_status' => 'ready',
            'subtotal' => 3500.00,
            'vat' => 262.50,
            'vat_rate' => 7.5,
            'grand_total' => 3762.50,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $readyOrder->id,
            'restaurant_menu_item_id' => $chicken->id,
            'quantity' => 1,
        ]);

        // Order 4: Completed (should NOT appear on KDS)
        $completedOrder = Order::create([
            'type' => 'table',
            'source_id' => $table1->id,
            'status' => 'completed',
            'tracking_status' => 'paid',
            'subtotal' => 2500.00,
            'vat' => 187.50,
            'vat_rate' => 7.5,
            'grand_total' => 2687.50,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $completedOrder->id,
            'restaurant_menu_item_id' => $jollof->id,
            'quantity' => 1,
        ]);
    }
}
