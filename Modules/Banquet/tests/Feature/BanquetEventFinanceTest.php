<?php

namespace Modules\Banquet\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Banquet\Database\Seeders\BanquetEventSeeder;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetPayment;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BanquetEventFinanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        JournalEntry::query()->delete();

        (new ChartOfAccountsSeeder)->run();
        (new BanquetEventSeeder)->run();
    }

    public function test_seeder_creates_banquet_events(): void
    {
        $this->assertSame(4, BanquetOrder::count());

        $order = BanquetOrder::where('order_id', sprintf('%04d-%d', 1, now()->year))->first();

        $this->assertNotNull($order, 'Wedding order should exist');
        $this->assertNotNull($order->customer, 'Wedding order should have a customer');
        $this->assertSame(1, $order->eventDays->count());
        $this->assertSame(2, $order->eventDays->first()->menuItems->count());
        $this->assertSame(2, $order->payments->count());
        $this->assertSame(180, $order->eventDays->first()->guest_count);
        $this->assertNotNull($order->eventDays->first()->venue, 'Event day should link to a venue');
        $this->assertNotNull($order->eventDays->first()->style, 'Event day should link to a setup style');
    }

    public function test_each_payment_posts_to_banquet_revenue_4200(): void
    {
        $payments = BanquetPayment::all();

        $this->assertGreaterThanOrEqual(5, $payments->count());

        foreach ($payments as $payment) {
            $entry = JournalEntry::where('reference_type', 'banquet_payment')
                ->where('reference_id', $payment->id)
                ->first();

            $this->assertNotNull($entry, "Missing journal entry for payment {$payment->id}");
            $this->assertSame("SALE-banquet_payment-{$payment->id}", $entry->entry_number);

            $revenueLine = $entry->lines->firstWhere('credit', '>', 0);
            $this->assertNotNull($revenueLine, "Entry {$entry->entry_number} should have a credit line");
            $this->assertSame(
                '4200',
                $revenueLine->account->code,
                'Banquet payments must credit GL 4200 (Banquet Revenue)'
            );
        }
    }

    public function test_payment_method_maps_to_correct_asset_account(): void
    {
        $expected = [
            'cash' => '1000',
            'transfer' => '1100',
            'cheque' => '1100',
            'pos' => '1100',
        ];

        foreach (BanquetPayment::all() as $payment) {
            $entry = JournalEntry::where('reference_type', 'banquet_payment')
                ->where('reference_id', $payment->id)
                ->firstOrFail();

            $debitLine = $entry->lines->firstWhere('debit', '>', 0);

            $this->assertNotNull($debitLine, "Entry {$entry->entry_number} should have a debit line");
            $this->assertSame(
                $expected[$payment->payment_method] ?? '1100',
                $debitLine->account->code,
                "{$payment->payment_method} payment should debit correct asset account"
            );
        }
    }

    public function test_all_payment_entries_are_balanced(): void
    {
        foreach (JournalEntry::where('reference_type', 'banquet_payment')->get() as $entry) {
            $totalDebit = (float) $entry->lines->sum('debit');
            $totalCredit = (float) $entry->lines->sum('credit');

            $this->assertEqualsWithDelta(
                $totalDebit,
                $totalCredit,
                0.005,
                "Entry {$entry->entry_number} is not balanced"
            );
        }
    }

    public function test_payment_statuses_reflect_seeded_payments(): void
    {
        $wedding = BanquetOrder::where('order_id', sprintf('%04d-%d', 1, now()->year))->first();
        $conference = BanquetOrder::where('order_id', sprintf('%04d-%d', 2, now()->year))->first();
        $birthday = BanquetOrder::where('order_id', sprintf('%04d-%d', 3, now()->year))->first();
        $board = BanquetOrder::where('order_id', sprintf('%04d-%d', 4, now()->year))->first();

        $this->assertSame('Partial', $wedding->payment_status);
        $this->assertSame('Fully Paid', $conference->payment_status);
        $this->assertSame('Fully Paid', $birthday->payment_status);
        $this->assertSame('Unpaid', $board->payment_status);

        $this->assertSame(1700000.0, (float) $wedding->paid_amount);
        $this->assertSame(150000.0, (float) $wedding->balance_due);
    }

    public function test_html_gateway_accounts_exist_for_banquet_posting(): void
    {
        foreach (['1000', '1100', '4200'] as $code) {
            $this->assertNotNull(
                ChartOfAccount::where('code', $code)->first(),
                "GL account $code should exist after seeding"
            );
        }
    }

    public function test_dashboard_renders_event_calendar_and_venue_density(): void
    {
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_banquet_dashboard', 'guard_name' => 'web']);
        $user->givePermissionTo('access_banquet_dashboard');

        $response = $this->actingAs($user)->get(route('banquet.index'));

        $response->assertOk();
        $response->assertSee('Event Calendar');
        $response->assertSee('Venue Density');
        $response->assertSee('Adamawa Hall');
        $response->assertSee(sprintf('%04d-%d', 1, now()->year));
    }

    public function test_calendar_accepts_month_param_and_shows_seeded_events(): void
    {
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_banquet_dashboard', 'guard_name' => 'web']);
        $user->givePermissionTo('access_banquet_dashboard');

        $month = now()->format('Y-m');

        $response = $this->actingAs($user)->get(route('banquet.index', ['month' => $month]));

        $response->assertOk();
        $response->assertSee('Adamawa Hall');
        $response->assertSee('Pool Party');
        $response->assertSee(now()->format('F Y'));
    }

    public function test_recording_payment_via_http_posts_to_coa(): void
    {
        $posting = app(PostingService::class);

        $user = User::factory()->create();
        foreach (['access_banquet_dashboard', 'banquet.create'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $user->givePermissionTo(['access_banquet_dashboard', 'banquet.create']);

        $board = BanquetOrder::where('order_id', sprintf('%04d-%d', 4, now()->year))->firstOrFail();
        $showUrl = route('banquet.orders.show', $board->order_id);
        $paymentAmount = (int) $board->balance_due;

        $response = $this->actingAs($user)->from($showUrl)->post(route('banquet.orders.payment.store', $board->order_id), [
            'amount' => $paymentAmount,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'reference' => 'WALKIN-CASH-77',
            'notes' => 'Recorded during integration test',
        ]);

        $response->assertSessionHas('success');

        $payment = BanquetPayment::where('banquet_order_id', $board->id)
            ->where('reference', 'WALKIN-CASH-77')
            ->first();

        $this->assertNotNull($payment, 'Payment should be persisted');

        $entry = JournalEntry::where('reference_type', 'banquet_payment')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($entry, 'Recording a payment via HTTP should post to finance');
        $this->assertSame("SALE-banquet_payment-{$payment->id}", $entry->entry_number);

        $this->assertSame('4200', $entry->lines->firstWhere('credit', '>', 0)->account->code);
        $this->assertSame('1000', $entry->lines->firstWhere('debit', '>', 0)->account->code);

        $totalDebit = (float) $entry->lines->sum('debit');
        $totalCredit = (float) $entry->lines->sum('credit');
        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.005);

        $board->refresh();
        $this->assertSame('Fully Paid', $board->payment_status);

        // Posting is idempotent — posting again for the same reference is a no-op.
        $this->assertNull(
            $posting->recordSale('banquet', $paymentAmount, 'cash', 'banquet_payment', $payment->id)
        );
    }
}
