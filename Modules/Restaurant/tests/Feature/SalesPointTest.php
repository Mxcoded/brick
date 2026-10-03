<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Restaurant\Filament\Pages\RestaurantReports;
use Modules\Restaurant\Filament\Resources\SalesPoints\SalesPointResource;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\SalesPoint;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Services\OrderPaymentService;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

/**
 * A cafe and a restaurant floor sharing one kitchen were indistinguishable in
 * the books: every sale credited 4100 Restaurant Revenue no matter which
 * counter took the order, so "how did the cafe do" had no answer.
 *
 * A sales point names the outlet and, optionally, the income account its sales
 * credit. It has to stay optional all the way down: an outlet without an account
 * is a label on the docket and nothing more, and that has to keep working.
 */
class SalesPointTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $waiter;

    private MenuItem $burger;

    private Table $table;

    private OrderPaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        // Both rates are pinned per test rather than inherited, because the
        // install's configured VAT rate is not zero and these assertions are
        // about which *revenue* account is credited, not about tax.
        RestaurantSetting::setValue('vat_rate', '0');
        RestaurantSetting::setValue('service_charge_rate', '0');

        $this->grantRestaurantRole(RoleEnum::WAITER->value);
        $this->waiter = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->waiter->assignRole(RoleEnum::WAITER->value);

        $category = MenuCategory::create(['name' => 'Test Foods']);
        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1500.00,
            'is_available' => true,
        ]);
        $this->table = Table::create(['number' => 'T1']);

        $this->payments = app(OrderPaymentService::class);
    }

    /**
     * A second waiter, because a user may only clock in once a day.
     */
    private function anotherWaiter(): User
    {
        $waiter = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $waiter->assignRole(RoleEnum::WAITER->value);

        return $waiter;
    }

    private function incomeAccount(string $code, string $name): ChartOfAccount
    {
        return ChartOfAccount::create([
            'code' => $code,
            'name' => $name,
            'type' => 'income',
            'normal_balance' => 'credit',
            'active' => true,
        ]);
    }

    private function salesPoint(array $overrides = []): SalesPoint
    {
        $code = $overrides['code'] ?? 'CAFE';

        return SalesPoint::create(array_merge([
            'name' => 'Cafe',
            'code' => $code,
            'is_active' => true,
        ], $overrides));
    }

    private function startShift(array $overrides = []): array
    {
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/shift/start', array_merge([
            'starting_cash' => 0,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        return $response->json('shift');
    }

    /**
     * Places and settles a POS order from the current open shift.
     */
    private function sell(array $overrides = []): Order
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', array_merge([
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        $order = Order::findOrFail($response->json('order_id'));
        $this->payments->takePayment($order, ['method' => 'cash']);

        return $order;
    }

    /**
     * @return array<string, float> account code => amount, for one side of the entry
     */
    private function entrySide(JournalEntry $entry, string $side): array
    {
        $amounts = [];

        foreach ($entry->lines()->with('account')->get() as $line) {
            $value = round((float) $line->{$side}, 2);
            if ($value > 0) {
                $amounts[$line->account->code] = $value;
            }
        }

        return $amounts;
    }

    private function creditsFor(Order $order): array
    {
        return $this->entrySide(
            JournalEntry::where('reference_type', 'restaurant_payment')
                ->where('reference_id', $order->payments()->first()->id)
                ->firstOrFail(),
            'credit',
        );
    }

    /*
     * Attributing the sale.
     */

    public function test_the_counter_chosen_at_clock_in_stamps_the_order(): void
    {
        $cafe = $this->salesPoint();

        $shift = $this->startShift(['sales_point_id' => $cafe->id]);
        $this->assertSame($cafe->id, $shift['sales_point_id']);

        $order = $this->sell();

        $this->assertSame($cafe->id, $order->sales_point_id);
        $this->assertTrue($order->salesPoint->is($cafe));
    }

    public function test_a_shift_without_a_counter_falls_back_to_the_property_default(): void
    {
        // A single-counter property should never have to think about this, and an
        // order must not end up unattributed when the picker was simply skipped.
        $cafe = $this->salesPoint(['is_default' => true]);

        $this->startShift(['sales_point_id' => null]);

        $this->assertSame($cafe->id, $this->sell()->sales_point_id);
    }

    public function test_a_property_with_no_sales_points_still_sells_and_stays_unattributed(): void
    {
        // The feature has to be opt-in. An install that never creates an outlet
        // must behave exactly as it did before.
        $this->startShift(['sales_point_id' => null]);

        $this->assertNull($this->sell()->sales_point_id);
    }

    public function test_an_inactive_counter_cannot_be_assigned_to_a_shift(): void
    {
        $closed = $this->salesPoint(['name' => 'Roof Bar', 'code' => 'ROOF', 'is_active' => false]);

        $this->actingAs($this->waiter)
            ->postJson('/restaurant-waiter/shift/start', ['sales_point_id' => $closed->id])
            ->assertJsonValidationErrors('sales_point_id');
    }

    public function test_the_order_keeps_its_counter_after_the_waiter_moves_on(): void
    {
        $cafe = $this->salesPoint();
        $grill = $this->salesPoint(['name' => 'Grill', 'code' => 'GRILL']);

        $this->startShift(['sales_point_id' => $cafe->id]);
        $order = $this->sell();

        // A different waiter, so this is a second counter rather than a second
        // shift for the same person.
        $other = $this->anotherWaiter();
        $this->actingAs($other)
            ->postJson('/restaurant-waiter/shift/start', ['sales_point_id' => $grill->id])
            ->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ])->assertOk();

        // Both orders are unpaid, but each still belongs to the counter it was
        // rung up on.
        $this->assertSame($cafe->id, $order->fresh()->sales_point_id);
        $this->assertSame($grill->id, Order::latest('id')->first()->sales_point_id);
    }

    public function test_a_guest_order_belongs_to_the_default_counter(): void
    {
        $cafe = $this->salesPoint(['is_default' => true]);
        $this->salesPoint(['name' => 'Grill', 'code' => 'GRILL']);

        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success', 'amount' => 1_000_000_00, 'channel' => 'card'],
            ]),
        ]);

        $response = $this->post('/restaurant/online/order/submit', [
            'payment_reference' => 'TEST-'.uniqid(),
            'customer_name' => 'Jane Doe',
            'customer_phone' => '08098765432',
            'delivery_address' => '456 Oak Avenue, Abuja',
        ]);

        $response->assertRedirect();
        preg_match('/\/order\/confirm\/(\d+)$/', $response->headers->get('Location'), $matches);

        // A delivery guest has no waiter and so no shift to inherit a counter
        // from. Without this they would be invisible in the outlet reports while
        // still crediting revenue.
        $this->assertSame($cafe->id, Order::findOrFail((int) $matches[1])->sales_point_id);
    }

    /*
     * The money.
     */

    public function test_sales_from_a_counter_with_its_own_account_credit_that_account(): void
    {
        $this->incomeAccount('4110', 'Cafe Revenue');
        $cafe = $this->salesPoint(['revenue_account_code' => '4110']);

        $this->startShift(['sales_point_id' => $cafe->id]);
        $order = $this->sell();

        // The whole point: revenue moved off the module account.
        $entry = JournalEntry::where('reference_type', 'restaurant_payment')
            ->where('reference_id', $order->payments()->first()->id)
            ->firstOrFail();

        $this->assertSame(['1000' => 1500.0], $this->entrySide($entry, 'debit'));
        $this->assertSame(['4110' => 1500.0], $this->entrySide($entry, 'credit'));
        $this->assertArrayNotHasKey('4100', $this->entrySide($entry, 'credit'));
    }

    public function test_a_counter_without_an_account_keeps_posting_to_the_module_revenue(): void
    {
        $cafe = $this->salesPoint();

        $this->startShift(['sales_point_id' => $cafe->id]);

        $this->assertSame(['4100' => 1500.0], $this->creditsFor($this->sell()));
    }

    public function test_two_counters_split_the_module_revenue_between_them(): void
    {
        $this->incomeAccount('4110', 'Cafe Revenue');
        $this->incomeAccount('4120', 'Grill Revenue');

        $cafe = $this->salesPoint(['revenue_account_code' => '4110']);
        $grill = $this->salesPoint(['name' => 'Grill', 'code' => 'GRILL', 'revenue_account_code' => '4120']);

        $other = $this->anotherWaiter();

        $this->startShift(['sales_point_id' => $cafe->id]);
        $cafeOrder = $this->sell();

        $this->actingAs($other)
            ->postJson('/restaurant-waiter/shift/start', ['sales_point_id' => $grill->id])
            ->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ])->assertOk();

        $grillOrder = Order::latest('id')->first();
        $this->payments->takePayment($grillOrder, ['method' => 'cash']);

        $this->assertSame(['4110' => 1500.0], $this->creditsFor($cafeOrder));
        $this->assertSame(['4120' => 1500.0], $this->creditsFor($grillOrder));
    }

    public function test_a_refund_comes_back_out_of_the_counter_account(): void
    {
        $this->incomeAccount('4110', 'Cafe Revenue');
        $cafe = $this->salesPoint(['revenue_account_code' => '4110']);

        $this->startShift(['sales_point_id' => $cafe->id]);
        $order = $this->sell();
        $this->payments->refundPayment($order->fresh(), 'Customer changed their mind');

        $entry = JournalEntry::where('entry_number', 'REF-restaurant_payment-'.$order->payments()->first()->id)
            ->firstOrFail();

        $this->assertTrue($entry->isBalanced());
        // If the reversal fell back to 4100 it would clear the wrong revenue and
        // leave the cafe account overstated for the rest of the period.
        $this->assertSame(['4110' => 1500.0], $this->entrySide($entry, 'debit'));
        $this->assertSame(['1000' => 1500.0], $this->entrySide($entry, 'credit'));
    }

    public function test_vat_and_service_charge_still_go_to_their_own_accounts(): void
    {
        // Overriding revenue must not swallow the tax or the charge, or a cafe
        // with its own account would quietly stop remitting VAT.
        RestaurantSetting::setValue('vat_rate', '7.5');
        RestaurantSetting::setValue('service_charge_rate', '10');

        $this->incomeAccount('4110', 'Cafe Revenue');
        $cafe = $this->salesPoint(['revenue_account_code' => '4110']);

        $this->startShift(['sales_point_id' => $cafe->id]);

        $this->assertSame([
            '4110' => 1500.0,
            '2200' => 112.5,
            '4400' => 150.0,
        ], $this->creditsFor($this->sell()));
    }

    /*
     * Reporting and management.
     */

    public function test_the_sales_report_breaks_revenue_down_by_counter(): void
    {
        $cafe = $this->salesPoint();

        $this->startShift(['sales_point_id' => $cafe->id]);
        $this->sell();

        $points = app(RestaurantReports::class)->salesSummary()['sales_points'];

        $this->assertCount(1, $points);
        $this->assertSame('Cafe', $points[0]['name']);
        $this->assertSame(1500.0, $points[0]['gross']);
        $this->assertSame(1500.0, $points[0]['net']);
        $this->assertSame(1, $points[0]['orders']);
    }

    public function test_unattributed_orders_are_still_reported_as_revenue(): void
    {
        // They posted to 4100, so leaving them out of the breakdown would make the
        // per-counter figures disagree with the ledger.
        $cafe = $this->salesPoint();

        $this->startShift(['sales_point_id' => $cafe->id]);
        $this->sell();

        $other = $this->anotherWaiter();
        $this->actingAs($other)->postJson('/restaurant-waiter/shift/start', [
            'starting_cash' => 0,
        ])->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        $this->actingAs($other)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ])->assertOk();

        $this->payments->takePayment(Order::latest('id')->first(), ['method' => 'cash']);

        $points = app(RestaurantReports::class)->salesSummary()['sales_points']
            ->keyBy('name');

        $this->assertSame(1500.0, $points['Cafe']['net']);
        $this->assertSame(1500.0, $points['Unassigned']['net']);
    }

    public function test_a_counter_that_took_orders_cannot_be_deleted_out_from_under_them(): void
    {
        $cafe = $this->salesPoint();
        $this->startShift(['sales_point_id' => $cafe->id]);
        $this->sell();

        // Retiring an outlet that has taken orders would strip historic revenue
        // of its attribution, so the button is refused outright. Deletion happens
        // through the panel's table action rather than a URL, hence the direct
        // assertion.
        $this->actingAs($this->manager());

        $this->assertFalse(SalesPointResource::canDelete($cafe));
    }

    public function test_an_unused_counter_can_be_deleted(): void
    {
        $cafe = $this->salesPoint(['name' => 'Roof Bar', 'code' => 'ROOF']);

        $this->actingAs($this->manager());

        $this->assertTrue(SalesPointResource::canDelete($cafe));

        $cafe->delete();

        $this->assertDatabaseMissing('restaurant_sales_points', ['id' => $cafe->id]);
    }

    public function test_the_settings_page_points_a_manager_at_the_sales_points(): void
    {
        $this->salesPoint();
        $this->salesPoint(['name' => 'Grill', 'code' => 'GRILL']);

        // Rates and counters are different shapes of thing, so the counters stay
        // a resource. Settings is where a manager starts looking, though, and a
        // dead end there is how a cafe ends up posting to 4100 forever.
        $response = $this->actingAs($this->manager())->get('/admin/restaurant/settings');

        $response->assertOk()
            ->assertSee('Manage sales points')
            ->assertSee('2 sales points are configured')
            ->assertSee(SalesPointResource::getUrl('index'), false);
    }

    public function test_the_settings_page_is_not_reachable_by_a_waiter(): void
    {
        // The page itself is gated on settings.read, which a waiter does not
        // hold, so the sales-points link inside it is unreachable to them
        // regardless. They get the counter list through the resource instead.
        $this->salesPoint();

        $this->actingAs($this->waiter)
            ->get('/admin/restaurant/settings')
            ->assertForbidden();
    }

    public function test_the_panel_screen_renders_for_a_manager(): void
    {
        $this->incomeAccount('4110', 'Cafe Revenue');
        $this->salesPoint(['revenue_account_code' => '4110']);

        $response = $this->actingAs($this->manager())->get('/admin/restaurant/sales-points');

        // The form and table only resolve their columns, options and queries
        // when rendered, so this is what catches a broken relationship or an
        // account selector that does not match the chart's actual shape.
        $response->assertOk()->assertSee('Cafe');

        // The form lives on the create screen, so its account selector and the
        // default toggle are only exercised there.
        $this->actingAs($this->manager())
            ->get('/admin/restaurant/sales-points/create')
            ->assertOk()
            ->assertSee('4110 - Cafe Revenue');
    }

    public function test_a_waiter_may_see_the_counters_but_not_change_them(): void
    {
        // The waiter needs to know which counter they are on; deciding which
        // revenue account backs it is a management call.
        $cafe = $this->salesPoint();
        $grill = $this->salesPoint(['name' => 'Grill', 'code' => 'GRILL']);

        $this->actingAs($this->waiter);

        $this->assertTrue(SalesPointResource::canViewAny());
        $this->assertFalse(SalesPointResource::canCreate());
        $this->assertFalse(SalesPointResource::canEdit($cafe));
        $this->assertFalse(SalesPointResource::canDelete($grill));
    }

    public function test_deleting_a_counter_leaves_its_historic_orders_in_place(): void
    {
        // A sale that really happened must not lose its attribution because the
        // outlet was retired. It becomes unattributed, which is visible.
        $cafe = $this->salesPoint();
        $this->startShift(['sales_point_id' => $cafe->id]);
        $order = $this->sell();

        $cafe->delete();

        // The posting is untouched: revenue already credited stays credited.
        $this->assertDatabaseHas('restaurant_orders', ['id' => $order->id, 'sales_point_id' => null]);
        $this->assertSame(['4100' => 1500.0], $this->creditsFor($order->fresh()));
    }

    private function manager(): User
    {
        $manager = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
        $manager->assignRole(RoleEnum::RESTAURANT_MANAGER->value);

        return $manager;
    }
}
