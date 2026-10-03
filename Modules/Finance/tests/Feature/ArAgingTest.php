<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Exports\ArAgingExport;
use Modules\Finance\Filament\Pages\ArAging;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalLine;
use Modules\Finance\Support\FinancePermission;
use Modules\Finance\Support\LedgerReport;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Services\CityLedgerService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A/R aging is the figure credit control and the auditors both work from, and it
 * has to agree with the AR control account rather than with any module's own
 * idea of what a customer owes.
 */
class ArAgingTest extends TestCase
{
    use DatabaseTransactions;

    private CityLedgerService $cityLedger;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        (new FinancePermissionSeeder)->run();

        foreach ([
            ['1200', 'Accounts Receivable', 'asset', 'debit'],
            ['4000', 'Room Revenue', 'income', 'credit'],
            ['1000', 'Cash', 'asset', 'debit'],
            ['1100', 'Bank', 'asset', 'debit'],
        ] as [$code, $name, $type, $normal]) {
            ChartOfAccount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'active' => true]
            );
        }

        $this->cityLedger = app(CityLedgerService::class);
        $this->user = User::factory()->create();
    }

    private function account(string $name = 'Acme Corp', string $terms = 'net30'): CityLedgerAccount
    {
        return $this->cityLedger->createAccount([
            'name' => $name,
            'credit_limit' => 5_000_000,
            'payment_terms' => $terms,
        ], $this->user->id);
    }

    /**
     * Raise a charge dated in the past so the aging buckets are reachable.
     */
    private function chargeOn(CityLedgerAccount $account, float $amount, int $daysAgo, ?string $reference = null)
    {
        return $this->cityLedger->postCharge(
            $account,
            $amount,
            "Charge $reference",
            $reference,
            null,
            null,
            $this->user->id,
            Carbon::now()->subDays($daysAgo)->toDateString(),
        );
    }

    private function payOn(CityLedgerAccount $account, float $amount, int $daysAgo)
    {
        return $this->cityLedger->recordPayment(
            $account,
            $amount,
            'bank_transfer',
            'Payment',
            null,
            $this->user->id,
            Carbon::now()->subDays($daysAgo)->toDateString(),
        );
    }

    public function test_an_unpaid_charge_lands_in_the_bucket_its_terms_due_it_in(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        // Charged 45 days ago on net30, so it fell due 15 days ago.
        $this->chargeOn($account, 100_000, 45, 'INV-001');

        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(100_000, $data['totalOutstanding'], 0.01);
        $this->assertEqualsWithDelta(100_000, $data['buckets']['days_1_30'], 0.01);
        $this->assertCount(1, $data['rows']);
        $this->assertSame('Acme Corp', $data['rows'][0]['label']);
    }

    public function test_a_charge_that_is_not_due_yet_is_current(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $this->chargeOn($account, 80_000, 5, 'INV-002');

        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(80_000, $data['buckets']['current'], 0.01);
    }

    public function test_buckets_track_how_late_each_charge_is(): void
    {
        $account = $this->account('Acme Corp', 'net15');

        // net15: 5 days late, 35 days late, 75 days late and 200 days late.
        $this->chargeOn($account, 10_000, 20, 'C1');
        $this->chargeOn($account, 20_000, 50, 'C2');
        $this->chargeOn($account, 30_000, 90, 'C3');
        $this->chargeOn($account, 40_000, 215, 'C4');

        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(10_000, $data['buckets']['days_1_30'], 0.01);
        $this->assertEqualsWithDelta(20_000, $data['buckets']['days_31_60'], 0.01);
        $this->assertEqualsWithDelta(30_000, $data['buckets']['days_61_90'], 0.01);
        $this->assertEqualsWithDelta(40_000, $data['buckets']['days_90_plus'], 0.01);
        $this->assertEqualsWithDelta(100_000, $data['totalOutstanding'], 0.01);
    }

    public function test_a_payment_settles_the_oldest_charge_first(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $old = $this->chargeOn($account, 60_000, 90, 'OLD');
        $this->chargeOn($account, 90_000, 10, 'NEW');

        // 50,000 only covers the older charge, so the newer one is still open.
        $this->payOn($account, 50_000, 5);

        $data = LedgerReport::receivableAging();
        $row = $data['rows'][0];

        $this->assertEqualsWithDelta(100_000, $data['totalOutstanding'], 0.01);
        $this->assertSame(2, $row['documents'], 'Both charges still have something open on them.');
        $this->assertEqualsWithDelta(10_000, $row['aging']['days_31_60'], 0.01, 'The older charge keeps its 10,000.');
        $this->assertEqualsWithDelta(90_000, $row['aging']['current'], 0.01);
    }

    public function test_a_fully_settled_charge_disappears_from_the_aging(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $this->chargeOn($account, 75_000, 40, 'INV-003');
        $this->payOn($account, 75_000, 5);

        $data = LedgerReport::receivableAging();

        $this->assertCount(0, $data['rows'], 'A paid-in-full customer is not overdue.');
        $this->assertEqualsWithDelta(0, $data['totalOutstanding'], 0.01);
    }

    public function test_a_payment_larger_than_the_oldest_charge_spills_into_the_next_one(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $this->chargeOn($account, 50_000, 120, 'OLD');   // due 90 days ago
        $this->chargeOn($account, 70_000, 60, 'MID');    // due 30 days ago
        $this->chargeOn($account, 90_000, 5, 'NEW');     // not yet due

        // 130,000 clears the 50,000 and 70,000 outright and lands 10,000 on the
        // newest charge, so only that one should still show as open.
        $this->payOn($account, 130_000, 1);

        $data = LedgerReport::receivableAging();
        $row = $data['rows'][0];

        $this->assertEqualsWithDelta(80_000, $data['totalOutstanding'], 0.01);
        $this->assertSame(1, $row['documents']);
        $this->assertEqualsWithDelta(80_000, $row['aging']['current'], 0.01);
        $this->assertEqualsWithDelta(0, $data['buckets']['days_90_plus'], 0.01, 'The 90-day debt is fully settled.');
    }

    public function test_money_paid_before_a_charge_was_raised_does_not_settle_it(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $this->payOn($account, 40_000, 10);
        $this->chargeOn($account, 60_000, 5, 'AFTER');

        // An on-account receipt that predates the charge cannot have been
        // against it, so the charge is still owed in full.
        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(60_000, $data['totalOutstanding'], 0.01);
    }

    public function test_a_payment_never_settles_another_customors_charge(): void
    {
        $a = $this->account('Acme Corp', 'net30');
        $b = $this->account('Beta Ltd', 'net30');

        $this->chargeOn($a, 100_000, 60, 'A1');
        $this->chargeOn($b, 50_000, 60, 'B1');
        $this->payOn($a, 100_000, 2);

        $data = LedgerReport::receivableAging();

        $this->assertCount(1, $data['rows'], 'Only the unsettled customer remains.');
        $this->assertSame('Beta Ltd', $data['rows'][0]['label']);
        $this->assertEqualsWithDelta(50_000, $data['totalOutstanding'], 0.01);
    }

    public function test_a_credit_note_reduces_what_is_owed(): void
    {
        $account = $this->account('Acme Corp', 'net30');

        $this->chargeOn($account, 200_000, 45, 'INV-004');

        $this->cityLedger->createCreditNote(
            $account,
            50_000,
            'Goodwill',
            'CN-004',
            $this->user->id,
            Carbon::now()->subDays(2)->toDateString(),
        );

        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(150_000, $data['totalOutstanding'], 0.01);
    }

    public function test_customers_are_kept_separate_and_ranked_by_what_they_owe(): void
    {
        $small = $this->account('Small Ltd', 'net30');
        $big = $this->account('Big Corp', 'net30');

        $this->chargeOn($small, 25_000, 40, 'S1');
        $this->chargeOn($big, 400_000, 40, 'B1');

        $data = LedgerReport::receivableAging();

        $this->assertCount(2, $data['rows']);
        $this->assertSame('Big Corp', $data['rows'][0]['label'], 'Largest debt first.');
        $this->assertSame('Small Ltd', $data['rows'][1]['label']);
    }

    public function test_the_aging_agrees_with_the_ar_control_account(): void
    {
        $a = $this->account('Acme Corp', 'net30');
        $b = $this->account('Beta Ltd', 'net45');

        $this->chargeOn($a, 120_000, 60, 'A1');
        $this->chargeOn($b, 80_000, 20, 'B1');
        $this->payOn($a, 30_000, 3);

        $arAccount = ChartOfAccount::where('code', '1200')->first();
        $lines = JournalLine::where('account_id', $arAccount->id)
            ->whereHas('journalEntry', fn ($e) => $e->where('status', 'posted'))
            ->get();

        $ledgerAr = (float) $lines->sum('debit') - (float) $lines->sum('credit');

        $data = LedgerReport::receivableAging();

        // The bucket total and the control account must not be able to drift.
        $this->assertEqualsWithDelta(170_000, $ledgerAr, 0.01);
        $this->assertEqualsWithDelta($ledgerAr, $data['totalOutstanding'], 0.01);
    }

    public function test_unposted_entries_are_excluded(): void
    {
        $account = $this->account('Acme Corp', 'net30');
        $this->chargeOn($account, 40_000, 40, 'INV-005');

        JournalEntry::query()
            ->where('reference_type', 'city_ledger_transaction')
            ->update(['status' => 'draft']);

        $data = LedgerReport::receivableAging();

        $this->assertEqualsWithDelta(0, $data['totalOutstanding'], 0.01);
    }

    public function test_the_aging_page_renders_for_a_reader(): void
    {
        $account = $this->account('Acme Corp', 'net30');
        $this->chargeOn($account, 150_000, 45, 'INV-006');

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web'));

        $this->actingAs($user);

        Livewire::test(ArAging::class)
            ->assertOk()
            ->assertSee('Acme Corp')
            ->assertSee('150,000.00');
    }

    public function test_the_aging_page_is_closed_to_someone_who_cannot_read_reports(): void
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->givePermissionTo([FinancePermission::DASHBOARD_ACCESS]);

        $this->assertFalse(ArAging::canAccess());
    }

    public function test_the_export_downloads_a_worksheet_with_the_aging(): void
    {
        Excel::fake();

        $account = $this->account('Acme Corp', 'net30');
        $this->chargeOn($account, 150_000, 45, 'INV-007');

        $export = new ArAgingExport;
        $flat = collect($export->array())->flatten()->filter(fn ($c) => is_string($c))->implode(' | ');

        $this->assertStringContainsString('ACCOUNTS RECEIVABLE AGING', $flat);
        $this->assertStringContainsString('Acme Corp', $flat);
        $this->assertStringContainsString('150,000.00', $flat);

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web'));

        $this->actingAs($user);

        Livewire::test(ArAging::class)
            ->assertOk()
            ->callAction('exportExcel');

        Excel::assertDownloaded('ar-aging-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_the_ar_aging_page_is_visible_in_finance_navigation(): void
    {
        $this->assertSame('finance/ar-aging', ArAging::getSlug());
    }
}
