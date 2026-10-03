<?php

namespace Modules\Restaurant\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\JournalEntry;
use Modules\Restaurant\Events\OrderPaid;
use Modules\Restaurant\Events\OrderRefunded;
use Modules\Restaurant\Events\PaymentRecorded;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Services\OrderPaymentService;
use Tests\TestCase;

/**
 * The invariant this suite exists to protect: a completed restaurant payment
 * has exactly one balanced ledger entry, and a refund reverses it.
 *
 * Posting used to be duplicated across the waiter terminal and the back office,
 * so this was only true as long as every copy stayed in step. The service
 * extraction makes it true by construction, and these tests fail loudly if a
 * future entry point bypasses it.
 */
class OrderPaymentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private OrderPaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();

        $this->payments = app(OrderPaymentService::class);
    }

    private function order(float $total = 5000, ?string $phone = null): Order
    {
        $category = MenuCategory::create(['name' => 'Payment Test '.$this->randomSuffix()]);
        $item = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Test Dish '.$this->randomSuffix(),
            'price' => $total,
        ]);

        $order = Order::create([
            'type' => 'walk_in',
            'status' => 'accepted',
            'tracking_status' => 'served',
            'subtotal' => $total,
            'grand_total' => $total,
            'customer_name' => 'Test Guest',
            'customer_phone' => $phone,
        ]);

        OrderItem::create([
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $item->id,
            'quantity' => 1,
            'instructions' => null,
        ]);

        return $order;
    }

    private function randomSuffix(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * @return Collection<int, JournalEntry>
     */
    private function entriesFor(int $paymentId)
    {
        return JournalEntry::where('reference_type', 'restaurant_payment')
            ->where('reference_id', $paymentId)
            ->where('status', 'posted')
            ->get();
    }

    /**
     * Signed total across every entry for one GL account: debits minus credits
     * when $debit is true, credits minus debits when it is not.
     *
     * @param  Collection<int, JournalEntry>  $entries
     */
    private function netMovement($entries, string $accountCode, bool $debit): float
    {
        return (float) $entries->sum(function (JournalEntry $entry) use ($accountCode, $debit) {
            $line = $entry->lines->firstWhere('account.code', $accountCode);

            if (! $line) {
                return 0.0;
            }

            return $debit
                ? (float) $line->debit - (float) $line->credit
                : (float) $line->credit - (float) $line->debit;
        });
    }

    public function test_payment_creates_a_balanced_ledger_entry(): void
    {
        $order = $this->order(5000);

        $result = $this->payments->takePayment($order, [
            'method' => 'cash',
            'amount_tendered' => 6000,
        ]);

        $payment = $result['payment'];

        $this->assertEquals(5000.0, (float) $payment->amount);
        $this->assertEquals(1000.0, $result['change_due']);
        $this->assertTrue($payment->finance_posted);

        $entries = $this->entriesFor($payment->id);
        $this->assertCount(1, $entries, 'exactly one ledger entry per payment');

        $entry = $entries[0];
        $this->assertEquals(5000.0, (float) $entry->lines->sum('debit'));
        $this->assertEquals(5000.0, (float) $entry->lines->sum('credit'));

        // Cash debits Cash on Hand (1000); every other method falls to Bank.
        $this->assertSame('1000', $entry->lines->firstWhere('debit', '>', 0)->account->code);
        $this->assertSame('4100', $entry->lines->firstWhere('credit', '>', 0)->account->code);
    }

    public function test_card_payment_debits_the_bank_instead_of_cash(): void
    {
        $order = $this->order(4000);

        $result = $this->payments->takePayment($order, ['method' => 'card']);

        $entry = $this->entriesFor($result['payment']->id)[0];

        $this->assertSame('1100', $entry->lines->firstWhere('debit', '>', 0)->account->code);
    }

    public function test_paying_in_full_completes_the_order_and_dispatches(): void
    {
        $order = $this->order(3000);

        $events = [];
        Event::listen(OrderPaid::class, function () use (&$events) {
            $events[] = 'paid';
        });
        Event::listen(PaymentRecorded::class, function () use (&$events) {
            $events[] = 'recorded';
        });

        $result = $this->payments->takePayment($order, ['method' => 'card']);

        $this->assertTrue($result['fully_paid']);
        $this->assertEquals(0.0, $result['balance_remaining']);

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertSame('paid', $order->tracking_status);

        $this->assertContains('paid', $events);
        $this->assertContains('recorded', $events);
    }

    public function test_partial_payment_leaves_the_order_open(): void
    {
        $order = $this->order(5000);

        $result = $this->payments->takePayment($order, [
            'method' => 'cash',
            'amount' => 2000,
        ]);

        $this->assertFalse($result['fully_paid']);
        $this->assertEquals(3000.0, $result['balance_remaining']);

        $order->refresh();
        $this->assertNotSame('completed', $order->status);
    }

    public function test_payment_is_capped_at_the_balance_due(): void
    {
        $order = $this->order(2000);

        // Overpayment must not create a receivable or a negative change figure.
        $result = $this->payments->takePayment($order, [
            'method' => 'cash',
            'amount' => 9999,
        ]);

        $this->assertEquals(2000.0, (float) $result['payment']->amount);
        $this->assertTrue($result['fully_paid']);
    }

    public function test_paying_a_settled_order_is_rejected(): void
    {
        $order = $this->order(1000);
        $this->payments->takePayment($order, ['method' => 'cash']);

        $this->expectExceptionMessage('No balance due.');

        $this->payments->takePayment($order->fresh(), ['method' => 'cash']);
    }

    public function test_two_partial_payments_produce_two_entries(): void
    {
        $order = $this->order(4000);

        $first = $this->payments->takePayment($order, ['method' => 'cash', 'amount' => 1500]);
        $second = $this->payments->takePayment($order->fresh(), ['method' => 'card', 'amount' => 2500]);

        $this->assertCount(1, $this->entriesFor($first['payment']->id));
        $this->assertCount(1, $this->entriesFor($second['payment']->id));
        $this->assertTrue($second['fully_paid']);

        $order->refresh();
        $this->assertSame('paid', $order->tracking_status);
    }

    public function test_refund_reverses_the_original_entry(): void
    {
        $order = $this->order(5000);
        $result = $this->payments->takePayment($order, ['method' => 'cash']);
        $payment = $result['payment'];

        $events = [];
        Event::listen(OrderRefunded::class, function ($event) use (&$events) {
            $events[] = $event->reason;
        });

        $refunded = $this->payments->refundPayment($order->fresh(), 'Customer sent it back');

        $this->assertSame('refunded', $refunded->status);
        $this->assertNotNull($refunded->refunded_at);
        $this->assertSame('Customer sent it back', $refunded->refund_reason);
        $this->assertSame(['Customer sent it back'], $events);

        // Sale plus reversal, both balanced. The reversal debits revenue and
        // credits cash, so cash nets back to zero and revenue nets back to zero
        // — that cancellation, not an equal pair of totals, is what "reversed"
        // has to mean.
        $entries = $this->entriesFor($payment->id);
        $this->assertCount(2, $entries);

        foreach ($entries as $entry) {
            $this->assertEquals(
                (float) $entry->lines->sum('debit'),
                (float) $entry->lines->sum('credit'),
                'each entry balances on its own'
            );
        }

        $cashMovement = $this->netMovement($entries, '1000', debit: true);
        $revenueMovement = $this->netMovement($entries, '4100', debit: false);

        $this->assertEquals(0.0, $cashMovement, 'cash returns to its opening balance');
        $this->assertEquals(0.0, $revenueMovement, 'revenue is unwound');

        $order->refresh();
        $this->assertSame('refunded', $order->tracking_status);
    }

    public function test_refund_requires_a_paid_order(): void
    {
        $order = $this->order(1000);

        $this->expectExceptionMessage('Only paid orders can be refunded.');

        $this->payments->refundPayment($order, 'Too early');
    }

    public function test_refund_of_an_unposted_payment_creates_no_reversal(): void
    {
        $order = $this->order(2000);
        $result = $this->payments->takePayment($order, ['method' => 'cash']);
        $payment = $result['payment'];

        // Simulates a ledger outage at sale time: the sale is on record, the
        // entry is not.
        $payment->forceFill(['finance_posted' => false])->save();

        $this->payments->refundPayment($order->fresh(), 'Refunded after outage');

        // Still only the original attempt's entry, and no credit invented
        // against a sale the ledger never saw.
        $entries = $this->entriesFor($payment->id);
        $this->assertCount(1, $entries);
        $this->assertEquals(2000.0, (float) $entries[0]->lines->sum('debit'));
    }

    public function test_reconcile_posts_a_payment_the_service_missed(): void
    {
        $order = $this->order(1500);
        $result = $this->payments->takePayment($order, ['method' => 'cash']);
        $payment = $result['payment'];

        $payment->forceFill(['finance_posted' => false])->save();

        $this->artisan('restaurant:reconcile-finance')
            ->assertExitCode(0);

        $this->assertTrue($payment->fresh()->finance_posted);
        $this->assertCount(1, $this->entriesFor($payment->id));
    }

    public function test_reconcile_dry_run_changes_nothing(): void
    {
        $order = $this->order(1500);
        $result = $this->payments->takePayment($order, ['method' => 'cash']);
        $payment = $result['payment'];

        $payment->forceFill(['finance_posted' => false])->save();

        $this->artisan('restaurant:reconcile-finance --dry-run')
            ->assertExitCode(0);

        $this->assertFalse($payment->fresh()->finance_posted);
    }

    public function test_reconcile_is_idempotent(): void
    {
        $order = $this->order(1500);
        $result = $this->payments->takePayment($order, ['method' => 'cash']);
        $payment = $result['payment'];

        $payment->forceFill(['finance_posted' => false])->save();

        $this->artisan('restaurant:reconcile-finance')->assertExitCode(0);
        $this->artisan('restaurant:reconcile-finance')->assertExitCode(0);

        $this->assertCount(1, $this->entriesFor($payment->id), 'no duplicate entry on re-run');
    }

    public function test_reconcile_is_clean_when_everything_is_posted(): void
    {
        $order = $this->order(1500);
        $this->payments->takePayment($order, ['method' => 'cash']);

        $this->artisan('restaurant:reconcile-finance')
            ->expectsOutputToContain('All completed payments are posted')
            ->assertExitCode(0);
    }

    public function test_visit_counters_roll_forward_on_full_payment(): void
    {
        $phone = '+23480'.random_int(1000000, 9999999);

        $order = $this->order(5000, $phone);
        $this->payments->takePayment($order, ['method' => 'cash']);

        $customer = Customer::where('phone', $phone)->first();

        $this->assertNotNull($customer);
        $this->assertSame(1, $customer->visit_count);
        $this->assertEquals(5000.0, (float) $customer->total_spent);
        $this->assertSame(50, $customer->loyalty_points);
    }

    public function test_payment_without_a_phone_skips_the_customer_record(): void
    {
        $order = $this->order(2000);

        $result = $this->payments->takePayment($order, ['method' => 'cash']);

        $this->assertTrue($result['fully_paid']);
        $this->assertSame(0, Customer::count());
    }

    public function test_unpaid_completion_through_the_back_office_still_posts(): void
    {
        // The back office used to build its own payment and posting inline. It
        // must now go through the same service, or the invariant quietly breaks
        // for orders completed from the admin screen.
        $order = $this->order(2750);

        $this->payments->takePayment($order, [
            'method' => 'transfer',
            'amount_tendered' => 3000,
        ]);

        $payment = Payment::where('restaurant_order_id', $order->id)->first();

        $this->assertNotNull($payment);
        $this->assertTrue($payment->finance_posted);
        $this->assertCount(1, $this->entriesFor($payment->id));
    }
}
