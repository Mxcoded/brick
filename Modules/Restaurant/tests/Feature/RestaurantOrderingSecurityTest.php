<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

/**
 * Guards on the money and the customer data behind the two ordering front ends.
 *
 * The waiter terminal and the public menu both take money and both render
 * customer details, and both used to derive those from whatever the request
 * carried. These tests pin the server-side behaviour that fixes that, with the
 * real middleware stack running so the route guards are part of the assertion.
 */
class RestaurantOrderingSecurityTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $waiter;

    private MenuItem $burger;

    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->grantRestaurantRole(RoleEnum::WAITER->value);

        $this->waiter = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->waiter->assignRole(RoleEnum::WAITER->value);

        $category = MenuCategory::create(['name' => 'Ordering Security']);

        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1000.00,
            'is_available' => true,
        ]);

        $this->table = Table::create(['number' => 'SEC1']);

        RestaurantSetting::setValue('vat_rate', '7.5');
        RestaurantSetting::setValue('discount_limit', '500');
    }

    // ── POS pricing ────────────────────────────────────────────────────────

    /**
     * The cart keeps the price an item had when it was added. If the menu is
     * edited before the waiter settles, the order has to be priced from the
     * menu as it stands, not from the stale copy in the session.
     */
    public function test_pos_reprices_from_the_menu_at_submit(): void
    {
        $this->addToPosCart($this->burger->id, 2);

        $this->burger->update(['price' => 1200.00]);

        $order = $this->submitPosOrder(['source_type' => 'table', 'source_id' => $this->table->id]);

        $this->assertEquals(2400.00, (float) $order->subtotal);
        $this->assertEquals(1200.00, (float) $order->orderItems->first()->unit_price);
    }

    /**
     * A fixed discount larger than the order used to produce a negative VAT base
     * and a negative grand total, and that was written to the order. The
     * discount is now clamped to the subtotal.
     */
    public function test_a_discount_larger_than_the_order_cannot_go_negative(): void
    {
        // Raise the ceiling so the subtotal, not the ceiling, is what binds.
        RestaurantSetting::setValue('discount_limit', '1000000');

        $this->addToPosCart($this->burger->id, 1); // 1000 subtotal

        $order = $this->submitPosOrder([
            'source_type' => 'walk_in',
            'discount' => 99999,
            'discount_type' => 'fixed',
        ]);

        $this->assertLessThanOrEqual((float) $order->subtotal, (float) $order->discount);
        $this->assertEquals(1000.0, (float) $order->discount);
        $this->assertGreaterThanOrEqual(0.0, (float) $order->vat);
        $this->assertEquals(0.0, (float) $order->grand_total);
    }

    /**
     * With the configured ceiling in force, a discount beyond it is cut to the
     * ceiling rather than the order value, and the totals stay consistent.
     */
    public function test_the_discount_ceiling_applies_before_the_subtotal_does(): void
    {
        $this->addToPosCart($this->burger->id, 1); // 1000 subtotal, ceiling 500

        $order = $this->submitPosOrder([
            'source_type' => 'walk_in',
            'discount' => 99999,
            'discount_type' => 'fixed',
        ]);

        $this->assertEquals(500.0, (float) $order->discount);
        $this->assertEquals(37.5, (float) $order->vat);
        $this->assertEquals(537.5, (float) $order->grand_total);
    }

    /**
     * `discount_limit` is documented as the largest discount a waiter may apply
     * without a manager. The browser enforced it; the server did not, so the
     * ceiling was advisory.
     */
    public function test_a_fixed_discount_is_capped_at_the_configured_ceiling(): void
    {
        $this->addToPosCart($this->burger->id, 10); // 10,000 subtotal

        $order = $this->submitPosOrder([
            'source_type' => 'walk_in',
            'discount' => 9000,
            'discount_type' => 'fixed',
        ]);

        $this->assertEquals(500.0, (float) $order->discount);
    }

    public function test_a_percentage_discount_above_one_hundred_is_clamped(): void
    {
        $this->addToPosCart($this->burger->id, 2); // 2000 subtotal

        $order = $this->submitPosOrder([
            'source_type' => 'walk_in',
            'discount' => 500,
            'discount_type' => 'percentage',
        ]);

        $this->assertEquals(2000.0, (float) $order->discount);
        $this->assertEquals(0.0, (float) $order->grand_total);
    }

    public function test_pos_rejects_an_absurd_line_quantity(): void
    {
        $this->actingAs($this->waiter)
            ->postJson('/restaurant-waiter/pos/cart/add', [
                'item_id' => $this->burger->id,
                'quantity' => 100000,
            ])
            ->assertStatus(422);
    }

    public function test_pos_rejects_a_table_that_does_not_exist(): void
    {
        $this->addToPosCart($this->burger->id, 1);

        $this->actingAs($this->waiter)
            ->postJson('/restaurant-waiter/pos/order/submit', [
                'source_type' => 'table',
                'source_id' => 999999,
            ])
            ->assertStatus(422);

        $this->assertSame(0, Order::where('type', 'table')->count());
    }

    // ── Price snapshot ─────────────────────────────────────────────────────

    /**
     * A receipt is a document of the sale as it happened. Once the menu price
     * moves, re-printing an old receipt must not re-price it, and the frozen
     * line must not contradict the order total it was frozen alongside.
     */
    public function test_a_receipt_keeps_the_price_the_line_was_sold_at(): void
    {
        $this->addToPosCart($this->burger->id, 2);

        $order = $this->submitPosOrder(['source_type' => 'walk_in', 'customer_name' => 'Snapshot Guest']);

        $this->burger->update(['price' => 9999.00]);

        $response = $this->actingAs($this->waiter)
            ->get("/restaurant-waiter/order/{$order->id}/receipt");

        $response->assertOk();
        $response->assertSee('2,000');   // 2 x 1000 as sold
        $response->assertDontSee('19,998'); // 2 x 9999 must not appear

        $line = $order->orderItems()->first();
        $this->assertEquals(1000.0, (float) $line->resolvedUnitPrice());
        $this->assertEquals(2000.0, (float) $line->lineTotal());
    }

    public function test_a_line_written_before_the_snapshot_still_renders(): void
    {
        // A row with no snapshot, as the backfill leaves it when the menu item
        // has since been deleted.
        $order = Order::create([
            'type' => 'walk_in',
            'status' => 'pending',
            'tracking_status' => 'pending',
            'subtotal' => 500,
            'grand_total' => 500,
        ]);

        $line = new OrderItem([
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->burger->id,
            'quantity' => 1,
        ]);
        $line->unit_price = null;
        $line->save();

        $this->assertEquals(1000.0, (float) $line->fresh()->resolvedUnitPrice());
    }

    // ── Terminal order scoping ─────────────────────────────────────────────

    /**
     * The terminal takes money for orders that were served in the restaurant.
     * An online delivery order is settled on the website, and route binding
     * alone would let a waiter settle one they never served.
     */
    public function test_the_terminal_cannot_settle_an_online_order(): void
    {
        $online = Order::create([
            'type' => 'online',
            'status' => 'pending',
            'tracking_status' => 'pending',
            'customer_name' => 'Remote Guest',
            'customer_phone' => '08000000000',
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $this->actingAs($this->waiter)
            ->postJson("/restaurant-waiter/order/{$online->id}/pay", ['method' => 'cash'])
            ->assertForbidden();

        $this->actingAs($this->waiter)
            ->get("/restaurant-waiter/order/{$online->id}/receipt")
            ->assertForbidden();

        $this->assertSame(0, $online->payments()->count());
    }

    public function test_the_terminal_can_still_settle_a_table_order(): void
    {
        $order = Order::create([
            'type' => 'table',
            'source_id' => $this->table->id,
            'status' => 'pending',
            'tracking_status' => 'pending',
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $this->actingAs($this->waiter)
            ->postJson("/restaurant-waiter/order/{$order->id}/pay", ['method' => 'cash', 'amount_tendered' => 1000])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    /**
     * Money routes must not be reachable by a signed-in account that lacks the
     * capability. This is the part the old suite missed by disabling all
     * middleware.
     */
    public function test_a_user_without_payment_capability_cannot_settle(): void
    {
        $this->grantRestaurantRole(RoleEnum::ADMIN->value);
        $noPayments = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $order = Order::create([
            'type' => 'table',
            'source_id' => $this->table->id,
            'status' => 'pending',
            'tracking_status' => 'pending',
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $this->assertFalse($noPayments->can(RestaurantPermission::PAYMENTS_CREATE));

        $this->actingAs($noPayments)
            ->postJson("/restaurant-waiter/order/{$order->id}/pay", ['method' => 'cash'])
            ->assertForbidden();

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_the_pos_is_closed_to_a_signed_out_caller(): void
    {
        $this->postJson('/restaurant-waiter/pos/order/submit', ['source_type' => 'walk_in'])
            ->assertUnauthorized();
    }

    // ── Guest cart cannot dictate price ────────────────────────────────────

    /**
     * The bulk cart endpoint takes whole line items, price and name included.
     * It has to rebuild them from the menu so a guest cannot post its own
     * prices or write arbitrary keys into the session cart.
     */
    public function test_the_bulk_cart_endpoint_ignores_client_prices(): void
    {
        $this->postJson('/restaurant/online/order/add', [
            'order' => [
                [
                    'item_id' => $this->burger->id,
                    'quantity' => 2,
                    'price' => 1.00,
                    'name' => 'Free Burger',
                ],
            ],
        ])->assertOk();

        $cart = session('online_cart');

        $this->assertSame('Burger', $cart[0]['name']);
        $this->assertEquals(1000.0, (float) $cart[0]['price']);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function addToPosCart(int $itemId, int $quantity): void
    {
        $this->actingAs($this->waiter)
            ->postJson('/restaurant-waiter/pos/cart/add', [
                'item_id' => $itemId,
                'quantity' => $quantity,
            ])
            ->assertOk();
    }

    private function submitPosOrder(array $payload): Order
    {
        $response = $this->actingAs($this->waiter)
            ->postJson('/restaurant-waiter/pos/order/submit', $payload);

        $response->assertOk()->assertJson(['success' => true]);

        return Order::with('orderItems')->findOrFail((int) $response->json('order_id'));
    }
}
