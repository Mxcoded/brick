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
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Services\OrderPaymentService;
use Modules\Restaurant\Services\OrderPricingService;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

/**
 * A service charge used to be an invisible term inside grand_total: only the POS
 * applied one, nothing stored it, and the whole gross went to 4100 Restaurant
 * Revenue as if it were food.
 */
class ServiceChargeTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $waiter;

    private MenuItem $burger;

    private Table $table;

    private OrderPaymentService $payments;

    private OrderPricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

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
        $this->pricing = app(OrderPricingService::class);
    }

    private function charge(float $rate): void
    {
        RestaurantSetting::setValue('service_charge_rate', (string) $rate);
    }

    private function vat(float $rate): void
    {
        RestaurantSetting::setValue('vat_rate', (string) $rate);
    }

    private function posOrder(array $overrides = []): Order
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ])->assertOk();

        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', array_merge([
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        return Order::findOrFail($response->json('order_id'));
    }

    private function onlineOrder(int $quantity = 2): Order
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => $quantity,
        ])->assertOk();

        // Overstates the amount on purpose: only an underpayment is refused, so
        // the fixture does not have to track the moving total.
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

        return Order::findOrFail((int) $matches[1]);
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

    private function entryForPayment(Payment $payment): JournalEntry
    {
        return JournalEntry::where('reference_type', 'restaurant_payment')
            ->where('reference_id', $payment->id)
            ->firstOrFail();
    }

    /*
     * What the charge does to the order.
     */

    public function test_the_pos_stores_the_service_charge_as_a_figure_of_its_own(): void
    {
        $this->charge(10);

        $order = $this->posOrder();

        $this->assertEquals(3000, (float) $order->subtotal);
        $this->assertEquals(10, (float) $order->service_charge_rate);
        $this->assertEquals(300, (float) $order->service_charge);
        $this->assertEquals(3525, (float) $order->grand_total);
    }

    public function test_online_guests_are_charged_the_same_service_charge_as_a_table(): void
    {
        // The actual bug: only the POS applied a service charge, so the same
        // basket cost a delivery guest less than a seated one.
        $this->charge(10);

        $table = $this->posOrder();
        $online = $this->onlineOrder();

        $this->assertEquals((float) $table->service_charge, (float) $online->service_charge);
        $this->assertEquals(300, (float) $online->service_charge);
        $this->assertEquals((float) $table->grand_total, (float) $online->grand_total);
    }

    public function test_the_charge_falls_on_the_post_discount_base_and_vat_does_not_tax_it(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->posOrder(['discount' => 500, 'discount_type' => 'fixed']);

        $this->assertEquals(2500, (float) $order->subtotal - (float) $order->discount);
        $this->assertEquals(250, (float) $order->service_charge);
        // VAT is on the discounted subtotal only. Were the charge in the base,
        // this would be 268.75 and the order would over-charge by a few
        // naira every time.
        $this->assertEquals(187.5, (float) $order->vat);
        $this->assertEquals(2937.5, (float) $order->grand_total);
    }

    public function test_the_stored_columns_add_back_up_to_the_grand_total(): void
    {
        $this->vat(7.5);
        $this->charge(12.5);

        $order = $this->posOrder(['discount' => 20, 'discount_type' => 'percentage']);

        $sum = (float) $order->subtotal - (float) $order->discount
            + (float) $order->vat + (float) $order->service_charge;

        $this->assertEqualsWithDelta((float) $order->grand_total, $sum, 0.001);
    }

    public function test_nothing_is_charged_when_the_rate_is_zero(): void
    {
        $this->charge(0);

        $order = $this->posOrder();

        $this->assertEquals(0, (float) $order->service_charge);
        $this->assertEquals(3225, (float) $order->grand_total);
    }

    public function test_an_install_with_no_rate_set_charges_nothing(): void
    {
        // OrderPricingService's fallback and the Filament settings fallback have
        // to agree. Filament used to show 10% while the POS charged 0%, so
        // opening and saving the form switched a charge on silently.
        RestaurantSetting::where('key', 'service_charge_rate')->delete();

        $this->assertEquals(0, $this->pricing->quote(1000)['service_charge']);
    }

    /*
     * What the charge does to the ledger.
     */

    public function test_a_sale_splits_the_gross_across_revenue_tax_and_service_charge(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->posOrder();
        $this->payments->takePayment($order, ['method' => 'cash']);

        $entry = $this->entryForPayment($order->payments()->first());
        $this->assertTrue($entry->isBalanced());

        // Cash is debited the whole amount the customer handed over.
        $this->assertSame(['1000' => 3525.0], $this->entrySide($entry, 'debit'));

        // And it is credited three ways, not swallowed whole by revenue.
        $this->assertSame([
            '4100' => 3000.0,
            '2200' => 225.0,
            '4400' => 300.0,
        ], $this->entrySide($entry, 'credit'));
    }

    public function test_the_service_charge_account_is_only_used_when_there_is_a_charge(): void
    {
        $this->vat(7.5);
        $this->charge(0);

        $order = $this->posOrder();
        $this->payments->takePayment($order, ['method' => 'cash']);

        $credits = $this->entrySide($this->entryForPayment($order->payments()->first()), 'credit');

        $this->assertArrayNotHasKey('4400', $credits);
        $this->assertSame(['4100' => 3000.0, '2200' => 225.0], $credits);
    }

    public function test_a_partial_payment_carries_only_its_share_of_the_tax_and_charge(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->posOrder();

        // Half the order settled. Crediting the order's full VAT and charge here
        // would book more tax than was collected.
        $this->payments->takePayment($order, ['method' => 'cash', 'amount' => 1762.5]);

        $entry = $this->entryForPayment($order->payments()->first());

        $this->assertSame([
            '4100' => 1500.0,
            '2200' => 112.5,
            '4400' => 150.0,
        ], $this->entrySide($entry, 'credit'));
    }

    public function test_a_refund_puts_each_component_back_where_it_came_from(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->posOrder();
        $this->payments->takePayment($order, ['method' => 'cash']);
        $this->payments->refundPayment($order->fresh(), 'Customer changed their mind');

        $entry = JournalEntry::where('entry_number', 'REF-restaurant_payment-'.$order->payments()->first()->id)
            ->firstOrFail();

        $this->assertTrue($entry->isBalanced());
        $this->assertSame(['1000' => 3525.0], $this->entrySide($entry, 'credit'));
        $this->assertSame([
            '4100' => 3000.0,
            '2200' => 225.0,
            '4400' => 300.0,
        ], $this->entrySide($entry, 'debit'));
    }

    public function test_an_online_sale_posts_the_same_split_as_a_pos_sale(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->onlineOrder();
        $payment = $order->payments()->first();

        $this->assertTrue($payment->finance_posted);
        $this->assertSame([
            '4100' => 3000.0,
            '2200' => 225.0,
            '4400' => 300.0,
        ], $this->entrySide($this->entryForPayment($payment), 'credit'));
    }

    /*
     * Reporting.
     */

    public function test_revenue_reports_net_of_the_charge_and_the_tax(): void
    {
        $this->vat(7.5);
        $this->charge(10);

        $order = $this->posOrder();
        $this->payments->takePayment($order, ['method' => 'cash']);

        $this->assertEquals(3000, $order->net_total);

        $summary = app(RestaurantReports::class)->salesSummary();

        $this->assertEquals(3525.0, $summary['total_sales']);
        $this->assertEquals(300.0, $summary['total_service_charge']);
        $this->assertEquals(225.0, $summary['total_vat']);
        // This is the figure the ledger credits to 4100, so it has to be what
        // the report calls revenue.
        $this->assertEquals(3000.0, $summary['net_sales']);
    }

    public function test_the_charge_revenue_account_exists_and_is_an_income_account(): void
    {
        $account = ChartOfAccount::where('code', '4400')->first();

        $this->assertNotNull($account, 'Service Charge Revenue account is missing from the chart of accounts.');
        $this->assertSame('income', $account->type);
        $this->assertSame('credit', $account->normal_balance);
    }
}
