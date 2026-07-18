<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WaiterPosTest extends TestCase
{
    use DatabaseTransactions;

    private User $waiter;

    private MenuItem $burger;

    private MenuItem $fries;

    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

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

        $category = MenuCategory::create(['name' => 'Test Foods']);
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
        $this->table = Table::create(['number' => 'T1']);
    }

    public function test_waiter_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id,
            'quantity' => 2,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals('Burger', $response->json('cart.0.name'));
        $this->assertEquals(2, $response->json('cart.0.quantity'));
    }

    public function test_waiter_can_add_multiple_items(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->fries->id, 'quantity' => 3,
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('cart'));
    }

    public function test_adding_same_item_increments_quantity(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals(3, $response->json('cart.0.quantity'));
    }

    public function test_waiter_can_update_cart_quantity(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/update', [
            'index' => 0, 'quantity' => 5,
        ]);

        $response->assertOk();
        $this->assertEquals(5, $response->json('cart.0.quantity'));
    }

    public function test_waiter_can_remove_item_from_cart(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->fries->id, 'quantity' => 2,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/remove', [
            'index' => 0,
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals('Fries', $response->json('cart.0.name'));
    }

    public function test_waiter_can_clear_cart(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/remove', [
            'index' => -1,
        ]);

        $response->assertOk();
        $this->assertEmpty($response->json('cart'));
    }

    public function test_waiter_can_get_cart_with_total(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 3,
        ]);
        $response = $this->actingAs($this->waiter)->getJson('/restaurant-waiter/pos/cart');

        $response->assertOk();
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals(4500, $response->json('total'));
    }

    public function test_waiter_can_submit_table_order(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $response->json('order_id'),
            'type' => 'table',
            'source_id' => $this->table->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $response->json('order_id'),
            'restaurant_menu_item_id' => $this->burger->id,
            'quantity' => 2,
        ]);
    }

    public function test_waiter_can_submit_walk_in_order(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->fries->id, 'quantity' => 1,
        ]);
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'walk_in',
            'customer_name' => 'John Doe',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $response->json('order_id'),
            'type' => 'walk_in',
            'source_id' => null,
            'customer_name' => 'John Doe',
            'status' => 'pending',
        ]);
    }

    public function test_submit_empty_cart_rejected(): void
    {
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ]);
        $response->assertStatus(422);
    }

    public function test_cart_cleared_after_order_submission(): void
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ]);
        $response = $this->actingAs($this->waiter)->getJson('/restaurant-waiter/pos/cart');

        $this->assertEmpty($response->json('cart'));
    }
}
