<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

class WaiterPosTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $waiter;

    private MenuItem $burger;

    private MenuItem $fries;

    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Only CSRF is bypassed. The terminal's real middleware (waiter-auth and
        // the per-route capability checks) is left in place on purpose, so
        // these tests exercise the route guards and not just the controller.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->grantRestaurantRole(RoleEnum::WAITER->value);

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

    public function test_waiter_login_redirects_to_the_pos_dashboard(): void
    {
        $this->post('/restaurant-waiter/login', [
            'email' => $this->waiter->email,
            'password' => 'password',
        ])->assertRedirect(route('restaurant.waiter.dashboard'));
    }

    public function test_waiter_login_ignores_a_stale_intended_url(): void
    {
        // A leftover intended URL (here a path that does not exist) used to be
        // replayed by redirect()->intended(), which is what sent waiters to a
        // 404 instead of the terminal.
        $this->withSession(['url.intended' => '/restaurant-waiter/does-not-exist']);

        $this->post('/restaurant-waiter/login', [
            'email' => $this->waiter->email,
            'password' => 'password',
        ])->assertRedirect(route('restaurant.waiter.dashboard'));
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

    public function test_dashboard_renders_with_named_route_map(): void
    {
        $response = $this->actingAs($this->waiter)->get('/restaurant-waiter/dashboard');

        $response->assertOk();

        // The terminal builds every AJAX and form URL from this map, so a route
        // that is renamed or removed must fail here rather than at click time.
        $response->assertSee('window.waiterRoutes', false);
        $response->assertSee('/restaurant-waiter/pos/cart/add', false);
        $response->assertSee('/restaurant-waiter/order/__ORDER__/receipt', false);
    }

    public function test_the_terminal_keeps_sold_out_items_visible_for_staff(): void
    {
        // A sold-out dish is hidden from the public menu, but the waiter still
        // needs to see it next to its category so they do not promise it to a
        // guest. This is the behaviour the public table menu used to carry.
        $this->burger->update(['is_available' => false]);

        $this->actingAs($this->waiter)
            ->get('/restaurant-waiter/dashboard')
            ->assertOk()
            ->assertSee('Burger');
    }

    /**
     * Card is taken on the venue's own terminal and recorded by the waiter.
     * Paystack is the online customer's channel, so the counter must not pull
     * in its widget or leak its key to anonymous staff.
     */
    public function test_the_pos_does_not_collect_payments_through_paystack(): void
    {
        $this->actingAs($this->waiter)
            ->get('/restaurant-waiter/dashboard')
            ->assertOk()
            ->assertDontSee('js.paystack.co', false)
            ->assertDontSee('Paystack', false);
    }

    public function test_pos_settings_do_not_expose_a_paystack_key(): void
    {
        $response = $this->actingAs($this->waiter)->getJson('/restaurant-waiter/pos/settings');

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertArrayNotHasKey('paystack_public_key', $response->json('settings'));
    }

    /**
     * The terminal hides the public navigation, and that navigation never
     * carried a sign-out link, so without one the waiter is on the POS until
     * their session expires.
     */
    public function test_the_pos_offers_a_way_to_sign_out(): void
    {
        $response = $this->actingAs($this->waiter)->get('/restaurant-waiter/dashboard');

        $response->assertOk();
        $response->assertSee(route('restaurant.waiter.logout'), false);
        $response->assertSee('Sign out');
    }

    public function test_the_pos_says_who_is_signed_in(): void
    {
        // A till is shared between shifts, so the waiter needs to see whose
        // session the screen belongs to before they take an order.
        // assertSee() escapes the expectation itself, so the raw name is passed
        // here; pre-escaping it turns the apostrophe in "O'Kon" into a
        // double-encoded string that never matches.
        $this->actingAs($this->waiter)
            ->get('/restaurant-waiter/dashboard')
            ->assertOk()
            ->assertSee($this->waiter->name);
    }

    public function test_signing_out_returns_the_terminal_to_the_login_screen(): void
    {
        $response = $this->actingAs($this->waiter)->post('/restaurant-waiter/logout');

        $response->assertRedirect('/restaurant-waiter/login');
        $this->assertGuest();

        // And the terminal is closed behind them.
        $this->get('/restaurant-waiter/dashboard')->assertRedirect('/restaurant-waiter/login');
    }
}
