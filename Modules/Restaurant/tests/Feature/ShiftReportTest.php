<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Restaurant\Filament\Pages\RestaurantReports;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\WaiterShift;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The shift report on the Filament reports page.
 *
 * `reports.shift.read` was granted in the config map and enforced on the legacy
 * `/reports/shift/{shift}` route, but the Filament page listed it in
 * canAccess() with nothing behind it. A group manager holding only that
 * permission could open the reports screen and be shown an empty page -- the
 * permission was a dead end.
 *
 * The parity assertion is the substantive one. This screen and the legacy JSON
 * endpoint are read by whoever reconciles the till, and a cash figure that
 * differs between them is not a rounding curiosity, it is a manager deciding
 * whether a drawer is short.
 */
class ShiftReportTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $manager;

    private WaiterShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);

        $this->manager = $this->staffWith(RoleEnum::RESTAURANT_MANAGER->value);

        $this->shift = WaiterShift::create([
            'user_id' => $this->manager->id,
            'clock_in' => now()->subHours(6),
            'clock_out' => now()->subHours(1),
            'starting_cash' => 10000.00,
            'ending_cash' => 25000.00,
            'status' => 'closed',
        ]);
    }

    private function completedOrder(float $grandTotal, string $method, float $amount): Order
    {
        $order = Order::create([
            'type' => 'table',
            'shift_id' => $this->shift->id,
            'status' => OrderStatus::COMPLETED,
            'tracking_status' => OrderStatus::PAID,
            'grand_total' => $grandTotal,
        ]);

        Payment::create([
            'restaurant_order_id' => $order->id,
            'amount' => $amount,
            'method' => $method,
            'status' => 'completed',
            'finance_posted' => true,
        ]);

        return $order;
    }

    /**
     * The reports page with a shift chosen, driven the way the browser does it.
     *
     * The form is filled through the Livewire testable rather than by assigning
     * to `$page->form`, because the property is a Filament schema that only
     * resolves correctly through its own container binding.
     */
    private function shiftPage(?int $shiftId = null): RestaurantReports
    {
        return Livewire::test(RestaurantReports::class)
            ->fillForm(['period' => 'today', 'shift_id' => $shiftId])
            ->call('applyFilters')
            ->assertHasNoFormErrors()
            ->instance();
    }

    // ── The figures ────────────────────────────────────────────────────

    #[Test]
    public function it_reconciles_opening_cash_against_completed_sales(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);
        $this->completedOrder(2500.00, 'card', 2500.00);

        $this->actingAs($this->manager);

        $page = $this->shiftPage($this->shift->id);

        $shift = $page->selectedShift();

        $this->assertNotNull($shift);
        $this->assertSame(7500.0, $shift['total_sales']);
        $this->assertSame(2, $shift['order_count']);
        $this->assertSame(10000.0, $shift['starting_cash']);

        // 10,000 opening + 7,500 sales = 17,500 expected against 25,000 counted.
        $this->assertSame(17500.0, $shift['expected_ending_cash']);
        $this->assertSame(7500.0, $shift['discrepancy']);
    }

    #[Test]
    public function it_reports_a_short_drawer_as_a_negative_discrepancy(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);

        $this->shift->update(['ending_cash' => 14000.00]);

        $this->actingAs($this->manager);

        $page = $this->shiftPage($this->shift->id);

        // 10,000 + 5,000 expected, 14,000 counted: 1,000 short.
        $this->assertSame(-1000.0, $page->selectedShift()['discrepancy']);
    }

    #[Test]
    public function an_open_shift_awaits_a_count_instead_of_claiming_a_zero_discrepancy(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);
        $this->shift->update(['ending_cash' => null, 'clock_out' => null, 'status' => 'open']);

        $this->actingAs($this->manager);

        $page = $this->shiftPage($this->shift->id);

        $shift = $page->selectedShift();

        $this->assertNull($shift['ending_cash']);
        $this->assertNull($shift['discrepancy']);
    }

    #[Test]
    public function it_groups_takings_by_method(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);
        $this->completedOrder(2000.00, 'card', 2000.00);
        $this->completedOrder(1000.00, 'card', 1000.00);

        $this->actingAs($this->manager);

        $page = $this->shiftPage($this->shift->id);

        $methods = $page->selectedShift()['payment_methods'];

        $this->assertSame(1, $methods['cash']['count']);
        $this->assertSame(5000.0, $methods['cash']['total']);
        $this->assertSame(2, $methods['card']['count']);
        $this->assertSame(3000.0, $methods['card']['total']);
    }

    #[Test]
    public function uncompleted_orders_are_left_out_of_the_reconciliation(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);

        // A voided and a still-accepted order on the same shift must not be
        // counted as takings.
        Order::create([
            'type' => 'table',
            'shift_id' => $this->shift->id,
            'status' => OrderStatus::VOID,
            'tracking_status' => OrderStatus::CANCELLED,
            'grand_total' => 9000.00,
        ]);

        Order::create([
            'type' => 'table',
            'shift_id' => $this->shift->id,
            'status' => OrderStatus::ACCEPTED,
            'grand_total' => 7000.00,
        ]);

        $this->actingAs($this->manager);

        $page = $this->shiftPage($this->shift->id);

        $shift = $page->selectedShift();

        $this->assertSame(1, $shift['order_count']);
        $this->assertSame(5000.0, $shift['total_sales']);
    }

    // ── Parity with the legacy endpoint ────────────────────────────────

    #[Test]
    public function it_agrees_with_the_legacy_shift_report_endpoint(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);
        $this->completedOrder(2500.00, 'card', 2500.00);

        $this->actingAs($this->manager);

        $legacy = $this->getJson("/restaurant-admin/reports/shift/{$this->shift->id}")
            ->assertOk()
            ->json('shift');

        $page = $this->shiftPage($this->shift->id);
        $screen = $page->selectedShift();

        foreach ([
            'waiter',
            'starting_cash',
            'ending_cash',
            'expected_ending_cash',
            'total_sales',
            'order_count',
            'status',
        ] as $key) {
            $this->assertEquals(
                $legacy[$key],
                $screen[$key],
                "The shift report disagrees with the legacy endpoint on '{$key}'.",
            );
        }

        $this->assertEqualsWithDelta(
            (float) $legacy['discrepancy'],
            (float) $screen['discrepancy'],
            0.001,
        );
    }

    // ── Access ─────────────────────────────────────────────────────────

    #[Test]
    public function the_shift_section_is_only_rendered_for_a_holder_of_the_capability(): void
    {
        // Granted directly, with no role behind it: revoking a permission from a
        // user who also holds it through a role achieves nothing, which is a
        // trap worth stepping around explicitly here.
        $salesOnly = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $salesOnly->givePermissionTo(
            Permission::findOrCreate(RestaurantPermission::REPORTS_SALES_READ, 'web')
        );

        $this->actingAs($salesOnly->fresh());

        $this->assertTrue(RestaurantReports::canAccess());

        Livewire::test(RestaurantReports::class)
            ->assertOk()
            ->assertDontSee('Shift reconciliation')
            // Still a usable screen: the sales summary is unaffected.
            ->assertSee('Sales');
    }

    #[Test]
    public function a_shift_only_viewer_sees_the_reconciliation(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);

        $viewer = $this->staffWith(RoleEnum::WAITER->value);
        $viewer->givePermissionTo(Permission::findOrCreate(RestaurantPermission::REPORTS_SHIFT_READ, 'web'));

        $this->actingAs($viewer->fresh());

        Livewire::test(RestaurantReports::class)
            ->assertOk()
            ->assertSee('Shift reconciliation')
            ->assertSee('Choose a shift above');
    }

    #[Test]
    public function a_viewer_without_the_capability_gets_nothing_even_asking_for_a_shift(): void
    {
        $this->completedOrder(5000.00, 'cash', 5000.00);

        $viewer = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $viewer->givePermissionTo(
            Permission::findOrCreate(RestaurantPermission::REPORTS_SALES_READ, 'web')
        );

        $this->actingAs($viewer->fresh());

        // The state is set directly rather than through the form, because the
        // picker is hidden from this viewer. That is the shape of the attack:
        // hand-picking a shift id in the request and hoping the view copes.
        $page = Livewire::test(RestaurantReports::class)
            ->assertOk()
            ->set('data.shift_id', $this->shift->id)
            ->instance();

        // The gate lives inside selectedShift(), not only in the template, so a
        // crafted request cannot read another waiter's drawer.
        $this->assertNull($page->selectedShift());
    }

    #[Test]
    public function the_legacy_shift_route_still_enforces_the_capability(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->getJson("/restaurant-admin/reports/shift/{$this->shift->id}")->assertForbidden();
    }

    #[Test]
    public function no_shift_selected_is_a_prompt_rather_than_an_error(): void
    {
        $this->actingAs($this->manager);

        $page = $this->shiftPage();

        $this->assertNull($page->selectedShift());
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user->fresh();
    }
}
