<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoomChargeSplitBillTest extends TestCase
{
    use DatabaseTransactions;

    private User $waiter;

    private MenuItem $jollof;

    private MenuItem $chicken;

    private MenuItem $plantain;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_restaurant_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->waiter = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->waiter->assignRole(RoleEnum::ADMIN->value);

        $category = MenuCategory::firstOrCreate(['name' => 'Split Bill Test Items']);
        $this->jollof = MenuItem::firstOrCreate(
            ['name' => 'Jollof Rice (Split)'],
            ['restaurant_menu_categories_id' => $category->id, 'price' => 2500.00, 'is_available' => true]
        );
        $this->chicken = MenuItem::firstOrCreate(
            ['name' => 'Grilled Chicken (Split)'],
            ['restaurant_menu_categories_id' => $category->id, 'price' => 3500.00, 'is_available' => true]
        );
        $this->plantain = MenuItem::firstOrCreate(
            ['name' => 'Fried Plantain (Split)'],
            ['restaurant_menu_categories_id' => $category->id, 'price' => 1200.00, 'is_available' => true]
        );

        ChargeType::firstOrCreate(
            ['code' => 'restaurant'],
            ['name' => 'Restaurant', 'account_code' => '4100']
        );

        RestaurantSetting::setValue('enable_room_service', '1');
    }

    private function createActiveOrder(): Order
    {
        $table = Table::firstOrCreate(['number' => 'SPLIT-T1']);

        $order = Order::create([
            'type' => 'table',
            'source_id' => $table->id,
            'status' => 'accepted',
            'tracking_status' => 'ready',
            'subtotal' => 7200.00,
            'vat' => 540.00,
            'vat_rate' => 7.5,
            'grand_total' => 7740.00,
        ]);

        OrderItem::create([
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->jollof->id,
            'quantity' => 1,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->chicken->id,
            'quantity' => 1,
        ]);
        OrderItem::create([
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->plantain->id,
            'quantity' => 1,
        ]);

        return $order;
    }

    // ─── Guest Lookup Tests ──────────────────────────────────────────

    public function test_guest_lookup_returns_404_when_no_registration(): void
    {
        $response = $this->actingAs($this->waiter)->getJson(
            route('restaurant.waiter.guest.lookup', ['room' => '999'])
        );

        $response->assertNotFound();
        $response->assertJson(['success' => false]);
    }

    public function test_guest_lookup_validates_room_parameter(): void
    {
        $response = $this->actingAs($this->waiter)->getJson(
            route('restaurant.waiter.guest.lookup', [])
        );

        $response->assertStatus(422);
    }

    // ─── Split Order Tests ───────────────────────────────────────────

    public function test_split_even_creates_groups(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 2]
        );

        $response->assertOk()->assertJson(['success' => true]);

        $groups = $response->json('groups');
        $this->assertCount(2, $groups);
        $this->assertArrayHasKey('A', $groups);
        $this->assertArrayHasKey('B', $groups);
    }

    public function test_split_even_with_three_groups(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 3]
        );

        $response->assertOk();
        $groups = $response->json('groups');
        $this->assertCount(3, $groups);
        $this->assertArrayHasKey('A', $groups);
        $this->assertArrayHasKey('B', $groups);
        $this->assertArrayHasKey('C', $groups);
    }

    public function test_split_by_items_assigns_groups(): void
    {
        $order = $this->createActiveOrder();
        $items = $order->orderItems()->get();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            [
                'type' => 'items',
                'items' => [
                    ['id' => $items[0]->id, 'group' => 'A'],
                    ['id' => $items[1]->id, 'group' => 'B'],
                    ['id' => $items[2]->id, 'group' => 'A'],
                ],
            ]
        );

        $response->assertOk();
        $groups = $response->json('groups');
        $this->assertCount(2, $groups);

        $item0 = $items[0]->fresh();
        $this->assertEquals('A', $item0->split_group);
    }

    public function test_split_subtotals_match(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 2]
        );

        $response->assertOk();
        $groups = $response->json('groups');

        $totalSubtotal = 0;
        foreach ($groups as $groupData) {
            $totalSubtotal += $groupData['subtotal'];
        }

        $this->assertEqualsWithDelta(7200.00, $totalSubtotal, 1.0, 'Group subtotals should sum to order subtotal');
    }

    // ─── Payment Tests ───────────────────────────────────────────────

    public function test_cash_payment_completes_order(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 8000,
                'method' => 'cash',
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_payments', [
            'restaurant_order_id' => $order->id,
            'method' => 'cash',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => 'completed',
            'tracking_status' => 'paid',
        ]);
    }

    public function test_room_charge_requires_registration_id(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            ['method' => 'room_charge']
        );

        $response->assertStatus(422);
    }

    public function test_split_payment_reduces_amount_due(): void
    {
        $order = $this->createActiveOrder();

        // Split into 2 even groups
        $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 2]
        )->assertOk();

        // Pay group A
        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 3870,
                'method' => 'cash',
                'split_group' => 'A',
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_payments', [
            'restaurant_order_id' => $order->id,
            'method' => 'cash',
        ]);

        $order->refresh();
        $this->assertGreaterThan(0, $order->amount_due, 'Order should still have remaining balance after partial payment');
    }

    public function test_split_payment_full_pay_completes_order(): void
    {
        $order = $this->createActiveOrder();

        $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 2]
        )->assertOk();

        // Pay group A
        $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 3870,
                'method' => 'cash',
                'split_group' => 'A',
            ]
        )->assertOk();

        // Pay group B
        $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 3870,
                'method' => 'cash',
                'split_group' => 'B',
            ]
        )->assertOk();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => 'completed',
            'tracking_status' => 'paid',
        ]);
    }

    public function test_payment_creates_record_in_database(): void
    {
        $order = $this->createActiveOrder();

        $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 7740,
                'method' => 'cash',
            ]
        )->assertOk();

        $payment = Payment::where('restaurant_order_id', $order->id)->first();
        $this->assertNotNull($payment, 'Payment record should exist');
        $this->assertEquals('cash', $payment->method);
    }

    public function test_transfer_payment_with_reference(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->actingAs($this->waiter)->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            [
                'amount_tendered' => 7740,
                'method' => 'transfer',
                'reference' => 'TXN-12345',
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('restaurant_payments', [
            'restaurant_order_id' => $order->id,
            'method' => 'transfer',
            'reference' => 'TXN-12345',
        ]);
    }

    // ─── Auth Guard Tests ────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_split(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->postJson(
            route('restaurant.waiter.order.split', $order->id),
            ['type' => 'even', 'count' => 2]
        );

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_pay(): void
    {
        $order = $this->createActiveOrder();

        $response = $this->postJson(
            route('restaurant.waiter.order.pay', $order->id),
            ['method' => 'cash', 'amount_tendered' => 7740]
        );

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_lookup_guest(): void
    {
        $response = $this->getJson(
            route('restaurant.waiter.guest.lookup', ['room' => '101'])
        );

        $response->assertStatus(401);
    }
}
