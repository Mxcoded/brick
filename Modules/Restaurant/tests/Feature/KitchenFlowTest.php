<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KitchenFlowTest extends TestCase
{
    use DatabaseTransactions;

    private User $waiter;

    private MenuItem $burger;

    private MenuItem $fries;

    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $role = Role::firstOrCreate(['name' => RoleEnum::WAITER->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_restaurant_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->waiter = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->waiter->assignRole(RoleEnum::WAITER->value);

        $category = MenuCategory::create(['name' => 'Main Courses']);
        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1500.00,
        ]);
        $this->fries = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Fries',
            'price' => 800.00,
        ]);
        $this->table = Table::create(['number' => 'A1']);
    }

    public function test_full_waiter_to_kitchen_to_sale_flow(): void
    {
        // ── Step 1: Waiter adds items and submits order ──
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ]);
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->fries->id, 'quantity' => 1,
        ]);

        $submit = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ]);

        $submit->assertOk()->assertJson(['success' => true]);
        $orderId = $submit->json('order_id');

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $orderId,
            'type' => 'table',
            'source_id' => $this->table->id,
            'status' => 'pending',
            'tracking_status' => 'pending',
        ]);
        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $orderId,
            'restaurant_menu_item_id' => $this->burger->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $orderId,
            'restaurant_menu_item_id' => $this->fries->id,
            'quantity' => 1,
        ]);

        // ── Step 2: KDS sees the order ──
        $kds = $this->actingAs($this->waiter)->getJson('/restaurant-admin/kitchen/data');
        $kds->assertOk()->assertJson(['success' => true]);
        $kdsOrders = $kds->json('orders');
        $kdsOrderIds = array_column($kdsOrders, 'id');
        $this->assertContains($orderId, $kdsOrderIds, 'Order must appear in KDS data');

        $kdsOrder = collect($kdsOrders)->firstWhere('id', $orderId);
        $this->assertNotNull($kdsOrder);
        $this->assertEquals('pending', $kdsOrder['tracking_status']);
        $this->assertCount(2, $kdsOrder['order_items']);
        $this->assertEquals('Burger', $kdsOrder['order_items'][0]['menu_item']['name']);

        // ── Step 3: Kitchen accepts the order ──
        $accept = $this->actingAs($this->waiter)->postJson("/restaurant-admin/kitchen/order/{$orderId}/accept");
        $accept->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $orderId,
            'status' => 'accepted',
            'tracking_status' => 'preparing',
        ]);

        // ── Step 4: KDS now shows it as preparing ──
        $kds2 = $this->actingAs($this->waiter)->getJson('/restaurant-admin/kitchen/data');
        $kds2->assertOk();
        $updatedOrder = collect($kds2->json('orders'))->firstWhere('id', $orderId);
        $this->assertEquals('preparing', $updatedOrder['tracking_status']);

        // ── Step 5: Waiter processes payment ──
        $pay = $this->actingAs($this->waiter)->postJson("/restaurant-waiter/order/{$orderId}/pay", [
            'method' => 'cash',
            'amount_tendered' => 5000.00,
        ]);

        $pay->assertOk()->assertJson(['success' => true]);
        $pay->assertJsonStructure(['payment' => ['id', 'amount', 'method', 'change_due']]);
        $this->assertGreaterThan(0, $pay->json('payment.id'));

        $this->assertDatabaseHas('restaurant_payments', [
            'restaurant_order_id' => $orderId,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        // ── Step 6: Order is completed ──
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $orderId,
            'status' => 'completed',
            'tracking_status' => 'paid',
        ]);

        // ── Step 7: Payment has correct change ──
        $payment = Payment::where('restaurant_order_id', $orderId)->first();
        $order = Order::find($orderId);
        $expectedChange = 5000 - (float) $order->grand_total;
        $this->assertEquals($expectedChange, (float) $payment->change_due);
    }

    public function test_kitchen_accept_fails_for_already_accepted_order(): void
    {
        $order = Order::create([
            'type' => 'table',
            'status' => 'accepted',
            'tracking_status' => 'preparing',
        ]);

        $response = $this->actingAs($this->waiter)->postJson("/restaurant-admin/kitchen/order/{$order->id}/accept");
        $response->assertStatus(422);
    }

    public function test_kitchen_accept_pending_order(): void
    {
        $order = Order::create([
            'type' => 'table',
            'status' => 'pending',
            'tracking_status' => 'pending',
        ]);

        $response = $this->actingAs($this->waiter)->postJson("/restaurant-admin/kitchen/order/{$order->id}/accept");
        $response->assertOk();
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => 'accepted',
            'tracking_status' => 'preparing',
        ]);
    }

    public function test_kds_shows_only_active_orders(): void
    {
        $active = Order::create([
            'type' => 'table',
            'status' => 'accepted',
            'tracking_status' => 'preparing',
        ]);
        $completed = Order::create([
            'type' => 'table',
            'status' => 'completed',
            'tracking_status' => 'paid',
        ]);

        $response = $this->actingAs($this->waiter)->getJson('/restaurant-admin/kitchen/data');
        $response->assertOk();

        $ids = array_column($response->json('orders'), 'id');
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($completed->id, $ids);
    }
}
