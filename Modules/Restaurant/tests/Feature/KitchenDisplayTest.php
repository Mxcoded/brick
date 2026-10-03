<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Restaurant\Filament\Pages\KitchenDisplay;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The kitchen display, as a Filament page.
 *
 * The parity tests matter more than the feature tests here. The legacy
 * `kdsData` endpoint and the old Blade screen are still in use, so the Filament
 * board has to be a second *view* of the same tickets rather than a
 * redefinition of which orders the kitchen owes. If the two queries drift, a
 * table goes missing from one screen and nobody notices until a customer is
 * waiting on food.
 */
class KitchenDisplayTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantRestaurantRole(RoleEnum::WAITER->value);
        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
        $this->grantRestaurantRole(RoleEnum::STORE_KEEPER->value);
    }

    private function order(string $status, ?string $tracking): Order
    {
        return Order::create([
            'type' => 'table',
            'status' => $status,
            'tracking_status' => $tracking,
        ]);
    }

    /**
     * A mounted page, for asserting on the board rather than the markup.
     */
    private function page(): KitchenDisplay
    {
        return Livewire::test(KitchenDisplay::class)->assertOk()->instance();
    }

    /**
     * @return array<int, int>
     */
    private function boardIds(KitchenDisplay $page): array
    {
        return collect($page->board())
            ->flatMap(fn (array $column) => $column['orders']->pluck('id')->all())
            ->sort()
            ->values()
            ->all();
    }

    private function foodCategory(): MenuCategory
    {
        return MenuCategory::create(['name' => 'Main Courses']);
    }

    private function barCategory(): MenuCategory
    {
        return MenuCategory::create(['name' => 'Beverages']);
    }

    // ── Parity with the legacy endpoint ─────────────────────────────────

    #[Test]
    public function the_board_shows_exactly_what_the_legacy_endpoint_shows(): void
    {
        $pending = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);
        $preparing = $this->order(OrderStatus::ACCEPTED, OrderStatus::PREPARING);
        $ready = $this->order(OrderStatus::ACCEPTED, OrderStatus::READY);
        $nullTracking = $this->order(OrderStatus::PENDING, null);
        $paid = $this->order(OrderStatus::COMPLETED, OrderStatus::PAID);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $legacyIds = collect($this->getJson('/restaurant-admin/kitchen/data')
            ->assertOk()
            ->json('orders'))
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $this->assertSame($legacyIds, $this->boardIds($this->page()));

        // Spelled out, so a change to either side of the assertion is visible.
        $this->assertContains($pending->id, $legacyIds);
        $this->assertContains($preparing->id, $legacyIds);
        $this->assertContains($ready->id, $legacyIds);
        $this->assertContains($nullTracking->id, $legacyIds);
        $this->assertNotContains($paid->id, $legacyIds, 'A settled bill is not a kitchen ticket.');
    }

    #[Test]
    public function an_accepted_order_still_tracked_as_pending_stays_on_the_board(): void
    {
        // The legacy query returns this row, so the board must not drop it: no
        // column matches it except the pending one, and an order that vanishes
        // from the kitchen is a table that never gets cooked.
        $order = $this->order(OrderStatus::ACCEPTED, OrderStatus::PENDING);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $legacyIds = collect($this->getJson('/restaurant-admin/kitchen/data')
            ->assertOk()
            ->json('orders'))
            ->pluck('id')
            ->all();

        $this->assertContains($order->id, $legacyIds);
        $this->assertContains($order->id, $this->boardIds($this->page()));
    }

    #[Test]
    public function the_alarm_tracks_the_pending_column_not_every_live_order(): void
    {
        $pending = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);
        $nullTracking = $this->order(OrderStatus::PENDING, null);
        $accepted = $this->order(OrderStatus::ACCEPTED, OrderStatus::PREPARING);
        $ready = $this->order(OrderStatus::ACCEPTED, OrderStatus::READY);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $pendingIds = $this->page()->pendingIds();

        // Everything the kitchen has still to accept rings...
        $this->assertContains($pending->id, $pendingIds);
        $this->assertContains($nullTracking->id, $pendingIds);

        // ...and anything already picked up does not, so the tone stops.
        $this->assertNotContains($accepted->id, $pendingIds);
        $this->assertNotContains($ready->id, $pendingIds);

        // The count driving the alarm is the pending column, not the whole board.
        $this->assertCount(2, $pendingIds);
    }

    #[Test]
    public function the_board_wires_up_the_alarm(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            // The board declares the Alpine component and the mute control, so
            // the shared alarm (public/js/kds-alarm.js) has something to drive.
            ->assertSee('kdsBoard')
            ->assertSee('alarm.toggleMute()');
    }

    #[Test]
    public function the_board_renders_icons_instead_of_literal_directive_text(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            // Column headers used an unregistered @icon() directive, which the
            // compiler left as literal text on screen. A real icon component
            // turns it into markup (and leaves no `@icon(` behind).
            ->assertDontSee('@icon(', false)
            ->assertSee('toggleFullscreen()', false);
    }

    #[Test]
    public function the_legacy_board_offers_fullscreen_and_icon_station_markers(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->get('/restaurant-admin/kitchen')
            ->assertOk()
            // A shell that can take the whole screen, and a control for it.
            ->assertSee('id="kdsShell"', false)
            ->assertSee('toggleFullscreen()', false)
            // Station markers are Bootstrap Icons rather than emoji, so they
            // render the same on whichever machine is projecting.
            ->assertSee('bi-cup-straw', false)
            ->assertDontSee('🍸', false);
    }

    #[Test]
    public function the_legacy_kitchen_routes_still_work(): void
    {
        // Nothing about the migration may change the old screen.
        $order = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->get('/restaurant-admin/kitchen')->assertOk();

        $this->getJson('/restaurant-admin/kitchen/data')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->postJson("/restaurant-admin/kitchen/order/{$order->id}/accept")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => OrderStatus::ACCEPTED,
            'tracking_status' => OrderStatus::PREPARING,
        ]);
    }

    // ── Column placement ───────────────────────────────────────────────

    #[Test]
    public function orders_are_placed_in_the_right_column(): void
    {
        $pending = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);
        $preparing = $this->order(OrderStatus::ACCEPTED, OrderStatus::PREPARING);
        $ready = $this->order(OrderStatus::ACCEPTED, OrderStatus::READY);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $board = $this->page()->board();

        $this->assertContains($pending->id, $board[OrderStatus::PENDING]['orders']->pluck('id')->all());
        $this->assertContains($preparing->id, $board[OrderStatus::PREPARING]['orders']->pluck('id')->all());
        $this->assertContains($ready->id, $board[OrderStatus::READY]['orders']->pluck('id')->all());

        $this->assertCount(1, $board[OrderStatus::PENDING]['orders']);
        $this->assertCount(1, $board[OrderStatus::PREPARING]['orders']);
        $this->assertCount(1, $board[OrderStatus::READY]['orders']);
    }

    // ── Transitions ────────────────────────────────────────────────────

    #[Test]
    public function a_waiter_can_accept_a_pending_order_from_the_board(): void
    {
        $order = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('accept', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'status' => OrderStatus::ACCEPTED,
            'tracking_status' => OrderStatus::PREPARING,
        ]);
    }

    #[Test]
    public function the_kitchen_can_mark_ready_then_served(): void
    {
        $order = $this->order(OrderStatus::ACCEPTED, OrderStatus::PREPARING);

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('markReady', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'tracking_status' => OrderStatus::READY,
        ]);

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('markServed', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'tracking_status' => OrderStatus::SERVED,
        ]);
    }

    #[Test]
    public function an_order_cannot_skip_from_pending_to_ready(): void
    {
        $order = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('markReady', $order->id)
            ->assertHasNoErrors();

        // The order must be left alone when the move is refused.
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'tracking_status' => OrderStatus::PENDING,
        ]);
    }

    #[Test]
    public function a_settled_bill_cannot_be_moved_along_by_the_kitchen(): void
    {
        $order = $this->order(OrderStatus::COMPLETED, OrderStatus::PAID);

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('markServed', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'tracking_status' => OrderStatus::PAID,
        ]);
    }

    #[Test]
    public function a_read_only_kitchen_view_cannot_move_orders(): void
    {
        $order = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);

        // Store keeper can be given the kitchen read without the write.
        $viewer = $this->staffWith(RoleEnum::STORE_KEEPER->value);
        $viewer->givePermissionTo(
            Permission::findOrCreate(RestaurantPermission::KITCHEN_READ, 'web')
        );

        $this->actingAs($viewer->fresh());

        $this->assertTrue(KitchenDisplay::canAccess());
        $this->assertFalse((new KitchenDisplay)->canUpdate());

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->assertDontSee('Accept order')
            ->call('accept', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'tracking_status' => OrderStatus::PENDING,
        ]);
    }

    #[Test]
    public function a_deleted_order_is_reported_rather_than_fatal(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->call('accept', 99999999)
            ->assertHasNoErrors();
    }

    // ── Access and rendering ───────────────────────────────────────────

    #[Test]
    public function the_page_is_gated_by_the_kitchen_read_capability(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));
        $this->assertTrue(KitchenDisplay::canAccess());

        $this->actingAs($this->staffWith(RoleEnum::GUEST->value));
        $this->assertFalse(KitchenDisplay::canAccess());
    }

    #[Test]
    public function the_board_renders_with_station_tags(): void
    {
        $burger = MenuItem::create([
            'restaurant_menu_categories_id' => $this->foodCategory()->id,
            'name' => 'Burger',
            'price' => 1500.00,
        ]);

        $beer = MenuItem::create([
            'restaurant_menu_categories_id' => $this->barCategory()->id,
            'name' => 'Beer',
            'price' => 800.00,
        ]);

        $order = $this->order(OrderStatus::PENDING, OrderStatus::PENDING);
        $order->orderItems()->create(['restaurant_menu_item_id' => $burger->id, 'quantity' => 2]);
        $order->orderItems()->create(['restaurant_menu_item_id' => $beer->id, 'quantity' => 1]);

        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Preparing')
            ->assertSee('Ready to serve')
            ->assertSee('#'.$order->id)
            ->assertSee('Burger')
            ->assertSee('Beer')
            ->assertSee('Kitchen')
            ->assertSee('Bar')
            ->assertSee('Accept order');
    }

    #[Test]
    public function drinks_are_routed_to_the_bar_and_food_to_the_kitchen(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $page = $this->page();

        $this->assertSame('Bar', $page->stationFor('Beverages'));
        $this->assertSame('Kitchen', $page->stationFor('Main Courses'));

        // An unknown or missing category is kitchen work, exactly as the legacy
        // display had it, rather than an untagged ticket nobody owns.
        $this->assertSame('Kitchen', $page->stationFor(null));
        $this->assertSame('Kitchen', $page->stationFor('Something Added Later'));
    }

    #[Test]
    public function an_empty_board_still_renders(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        Livewire::test(KitchenDisplay::class)
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Preparing')
            ->assertSee('Ready to serve')
            ->assertDontSee('Accept order');
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user->fresh();
    }
}
