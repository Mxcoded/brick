<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Exports\ProfitLossExport;
use Modules\Finance\Filament\Pages\BalanceSheet;
use Modules\Finance\Filament\Pages\GeneralLedger;
use Modules\Finance\Filament\Pages\ProfitLoss;
use Modules\Finance\Filament\Pages\RevenueBySource;
use Modules\Finance\Filament\Pages\TrialBalance;
use Modules\Finance\Services\PostingService;
use Modules\Finance\Support\FinancePermission;
use Modules\Finance\Support\LedgerReport;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The reporting surface: the new hotel reports, and the fact that exporting is
 * a separate grant from reading.
 */
class FinanceReportExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
        (new FinancePermissionSeeder)->run();
    }

    private function reader(string ...$extra): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web'));

        if ($extra) {
            $user->givePermissionTo($extra);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $user;
    }

    /**
     * A user who may read the reports but was deliberately not granted any
     * export capability.
     */
    private function screenOnlyReader(): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->givePermissionTo([
            FinancePermission::DASHBOARD_ACCESS,
            FinancePermission::REPORTS_READ,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_revenue_by_source_groups_posted_revenue_under_its_operating_area(): void
    {
        app(PostingService::class)->recordSale('restaurant', 45_000, 'cash', 'restaurant_payment', 1);
        app(PostingService::class)->recordSale('website', 20_000, 'cash', 'booking', 2);

        $data = LedgerReport::revenueBySource();

        $this->assertEqualsWithDelta(65_000, $data['totalRevenue'], 0.01);

        $labels = $data['rows']->pluck('label')->all();

        // 4100 is written by a single module, so it is named after that area.
        $this->assertContains('Restaurant', $labels);

        // The website and the front desk deliberately share account 4000, so the
        // row is named after the GL line rather than pretending to split
        // direct and walk-in revenue that is posted to the same account.
        $this->assertContains('Room Revenue', $labels);
    }

    public function test_revenue_by_source_report_page_renders(): void
    {
        app(PostingService::class)->recordSale('banquet', 90_000, 'cash', 'banquet_order', 3);

        $this->actingAs($this->reader());

        Livewire::test(RevenueBySource::class)
            ->assertOk()
            ->assertSee('Banquet');
    }

    public function test_general_ledger_returns_posted_lines_with_a_running_balance(): void
    {
        app(PostingService::class)->recordSale('restaurant', 30_000, 'cash', 'restaurant_payment', 4);

        $data = LedgerReport::generalLedger(null);

        $this->assertNotEmpty($data['rows']);
        $this->assertEqualsWithDelta($data['totalCredit'], $data['totalDebit'], 0.01);

        // Every row carries a running balance for the report to display.
        foreach ($data['rows'] as $line) {
            $this->assertIsFloat($line->balance);
        }
    }

    public function test_general_ledger_can_be_filtered_to_one_account(): void
    {
        app(PostingService::class)->recordSale('restaurant', 15_000, 'cash', 'restaurant_payment', 5);

        $code = config('finance.accounts.revenue.restaurant', '4000');

        $data = LedgerReport::generalLedger($code);

        $this->assertSame($code, $data['account']?->code);
        $this->assertEqualsWithDelta(15_000, (float) $data['rows']->sum('credit'), 0.01);
    }

    public function test_general_ledger_page_renders(): void
    {
        app(PostingService::class)->recordSale('gym', 12_000, 'cash', 'gym_membership', 6);

        $this->actingAs($this->reader());

        Livewire::test(GeneralLedger::class)
            ->assertOk()
            ->assertSee('Account');
    }

    public function test_export_action_is_shown_when_the_user_may_export(): void
    {
        $this->actingAs($this->reader(FinancePermission::REPORTS_EXPORT));

        foreach ([TrialBalance::class, ProfitLoss::class, BalanceSheet::class, RevenueBySource::class, GeneralLedger::class] as $page) {
            Livewire::test($page)
                ->assertOk()
                ->assertActionVisible('exportExcel');
        }
    }

    public function test_export_action_is_hidden_without_the_export_permission(): void
    {
        // Read the report on screen, but take nothing away in a spreadsheet.
        $this->actingAs($this->screenOnlyReader());

        Livewire::test(TrialBalance::class)
            ->assertOk()
            ->assertActionHidden('exportExcel');
    }

    public function test_reading_reports_alone_does_not_grant_exporting(): void
    {
        $user = $this->screenOnlyReader();

        $this->assertTrue(FinancePermission::can($user, FinancePermission::REPORTS_READ));
        $this->assertFalse(FinancePermission::can($user, FinancePermission::REPORTS_EXPORT));
    }

    public function test_report_exports_produce_a_downloadable_xlsx(): void
    {
        Excel::fake();

        app(PostingService::class)->recordSale('restaurant', 55_000, 'cash', 'restaurant_payment', 7);

        $this->actingAs($this->reader(FinancePermission::REPORTS_EXPORT));

        Livewire::test(TrialBalance::class)
            ->callAction('exportExcel');

        Excel::assertDownloaded(
            Str::slug('Trial Balance').'-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function test_export_rows_carry_the_statement_totals(): void
    {
        app(PostingService::class)->recordSale('restaurant', 25_000, 'cash', 'restaurant_payment', 8);

        $export = new ProfitLossExport;
        $rows = $export->array();

        $flat = collect($rows)->flatten()->filter(fn ($cell) => is_string($cell))->implode(' | ');

        $this->assertStringContainsString('PROFIT & LOSS', $flat);
        $this->assertStringContainsString('TOTAL INCOME', $flat);
        $this->assertStringContainsString('25,000.00', $flat);
    }
}
