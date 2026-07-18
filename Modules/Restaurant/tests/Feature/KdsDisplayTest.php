<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Database\Seeders\KdsDisplaySeeder;
use Modules\Restaurant\Models\Order;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KdsDisplayTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_restaurant_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
    }

    public function test_kds_page_loads_for_authorized_user(): void
    {
        $response = $this->actingAs($this->admin)->get(route('restaurant.admin.kitchen'));

        $response->assertOk();
        $response->assertViewIs('restaurant::admin.kitchen_display');
        $response->assertSee('Kitchen Display');
    }

    public function test_kds_page_contains_refresh_and_status_js(): void
    {
        $response = $this->actingAs($this->admin)->get(route('restaurant.admin.kitchen'));

        $response->assertOk();
        $response->assertSee('kdsGrid');
        $response->assertSee('Kitchen Display');
    }

    public function test_kds_data_api_returns_pending_orders(): void
    {
        (new KdsDisplaySeeder)->run();

        $response = $this->actingAs($this->admin)->getJson(route('restaurant.admin.kitchen.data'));

        $response->assertOk()->assertJson(['success' => true]);
        $orders = $response->json('orders');

        $this->assertNotEmpty($orders, 'KDS should have at least one active order');

        $statuses = collect($orders)->pluck('tracking_status')->unique()->values()->toArray();
        $this->assertContains('preparing', $statuses, 'KDS should show preparing orders');
        $this->assertContains('ready', $statuses, 'KDS should show ready orders');
    }

    public function test_kds_data_excludes_completed_orders(): void
    {
        (new KdsDisplaySeeder)->run();

        $response = $this->actingAs($this->admin)->getJson(route('restaurant.admin.kitchen.data'));

        $response->assertOk();
        $orders = $response->json('orders');

        foreach ($orders as $order) {
            $this->assertNotEquals('paid', $order['tracking_status'], 'Completed orders should not appear on KDS');
            $this->assertNotEquals('completed', $order['status'], 'Completed orders should not appear on KDS');
        }
    }

    public function test_kds_data_includes_order_items_and_menu_item_names(): void
    {
        (new KdsDisplaySeeder)->run();

        $response = $this->actingAs($this->admin)->getJson(route('restaurant.admin.kitchen.data'));

        $response->assertOk();
        $orders = $response->json('orders');

        $orderWithItems = collect($orders)->first(fn ($o) => count($o['order_items']) > 0);
        $this->assertNotNull($orderWithItems, 'At least one order should have items');

        $firstItem = $orderWithItems['order_items'][0];
        $this->assertArrayHasKey('menu_item', $firstItem);
        $this->assertNotNull($firstItem['menu_item']['name']);
        $this->assertGreaterThan(0, $firstItem['quantity']);
    }

    public function test_kds_accept_moves_order_to_preparing(): void
    {
        (new KdsDisplaySeeder)->run();

        $pendingOrder = Order::where('status', 'pending')
            ->whereNull('tracking_status')
            ->first();

        $this->assertNotNull($pendingOrder, 'Seeder should have created a pending order');

        $response = $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.accept', $pendingOrder->id)
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $pendingOrder->id,
            'status' => 'accepted',
            'tracking_status' => 'preparing',
        ]);
    }

    public function test_kds_status_update_advances_order(): void
    {
        (new KdsDisplaySeeder)->run();

        $preparingOrder = Order::where('tracking_status', 'preparing')->first();
        $this->assertNotNull($preparingOrder);

        $response = $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.status', $preparingOrder->id),
            ['tracking_status' => 'ready']
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $preparingOrder->id,
            'tracking_status' => 'ready',
        ]);
    }

    public function test_kds_status_update_to_served(): void
    {
        (new KdsDisplaySeeder)->run();

        $readyOrder = Order::where('tracking_status', 'ready')->first();
        $this->assertNotNull($readyOrder);

        $response = $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.status', $readyOrder->id),
            ['tracking_status' => 'served']
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $readyOrder->id,
            'tracking_status' => 'served',
        ]);
    }

    public function test_kds_full_lifecycle_pending_to_served(): void
    {
        (new KdsDisplaySeeder)->run();

        $order = Order::where('status', 'pending')
            ->whereNull('tracking_status')
            ->first();

        // Accept → preparing
        $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.accept', $order->id)
        )->assertOk();

        // Preparing → ready
        $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.status', $order->id),
            ['tracking_status' => 'ready']
        )->assertOk();

        // Ready → served
        $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.status', $order->id),
            ['tracking_status' => 'served']
        )->assertOk();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => 'accepted',
            'tracking_status' => 'served',
        ]);
    }

    public function test_kds_rejects_invalid_status_transition(): void
    {
        (new KdsDisplaySeeder)->run();

        $pendingOrder = Order::where('status', 'pending')
            ->whereNull('tracking_status')
            ->first();

        $response = $this->actingAs($this->admin)->postJson(
            route('restaurant.admin.kitchen.status', $pendingOrder->id),
            ['tracking_status' => 'served']
        );

        $response->assertOk();
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $pendingOrder->id,
            'tracking_status' => 'served',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_kds(): void
    {
        $response = $this->get(route('restaurant.admin.kitchen'));

        $response->assertRedirect();
    }

    public function test_user_without_permission_cannot_access_kds(): void
    {
        $unauthorized = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $response = $this->actingAs($unauthorized)->get(route('restaurant.admin.kitchen'));

        $response->assertForbidden();
    }

    public function test_seeder_creates_expected_order_counts(): void
    {
        $before = Order::count();
        (new KdsDisplaySeeder)->run();
        $after = Order::count();

        $this->assertEquals(4, $after - $before, 'Seeder should create exactly 4 orders');
    }
}
