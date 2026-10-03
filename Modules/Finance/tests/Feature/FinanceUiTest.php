<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use App\Support\LandingPage;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Pages\Dashboard as AdminDashboard;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Filament\Pages\BalanceSheet;
use Modules\Finance\Filament\Pages\FinanceDashboard;
use Modules\Finance\Filament\Pages\ProfitLoss;
use Modules\Finance\Filament\Pages\TrialBalance;
use Modules\Finance\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Pages\CreateChartOfAccount;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Pages\ListChartOfAccounts;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\ViewJournalEntry;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Modules\Finance\Support\FinancePermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceUiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        (new ChartOfAccountsSeeder)->run();
        (new FinancePermissionSeeder)->run();

        JournalEntry::query()->delete();

        $this->user = User::factory()->create();
        $this->user->assignRole(Role::findOrCreate('finance', 'web'));
    }

    public function test_finance_pages_render_for_finance_user(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FinanceDashboard::class)->assertOk();
        Livewire::test(ListChartOfAccounts::class)->assertOk();
        Livewire::test(ListJournalEntries::class)->assertOk();
        Livewire::test(TrialBalance::class)->assertOk();
        Livewire::test(ProfitLoss::class)->assertOk();
        Livewire::test(BalanceSheet::class)->assertOk();
    }

    public function test_posted_entry_appears_in_trial_balance(): void
    {
        app(PostingService::class)
            ->recordSale('restaurant', 1000, 'cash', 'restaurant_payment', 1);

        $this->actingAs($this->user);

        Livewire::test(TrialBalance::class)
            ->assertOk()
            ->assertSee('1,000.00');
    }

    public function test_journal_show_displays_lines(): void
    {
        $entry = app(PostingService::class)
            ->recordSale('restaurant', 500, 'card', 'restaurant_payment', 2);

        $this->actingAs($this->user);

        Livewire::test(ViewJournalEntry::class, ['record' => $entry->getRouteKey()])
            ->assertOk()
            ->assertSee($entry->entry_number)
            ->assertSee('500.00');
    }

    public function test_chart_of_accounts_can_be_created(): void
    {
        // Creating an account is a senior-accountant capability; the junior
        // `finance` role is read-only over the chart of accounts.
        $this->assertFalse(
            FinancePermission::can($this->user, FinancePermission::CHART_OF_ACCOUNTS_CREATE)
        );

        $this->user->givePermissionTo(FinancePermission::CHART_OF_ACCOUNTS_CREATE);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user->fresh());

        Livewire::test(CreateChartOfAccount::class)
            ->fillForm([
                'code' => '9999',
                'name' => 'Test Clearing',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('finance_chart_of_accounts', ['code' => '9999']);
    }

    public function test_journal_post_action_requires_post_permission(): void
    {
        $entry = app(PostingService::class)
            ->recordExpense('restaurant', 250, 'cash', 'restaurant_payment', 9);

        // Force a draft so the post action is available.
        $entry->update(['status' => 'draft', 'posted_at' => null]);

        $role = Role::findByName('finance', 'web');
        $role->givePermissionTo(FinancePermission::JOURNAL_ENTRIES_POST);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user->fresh());

        Livewire::test(ListJournalEntries::class)
            ->assertOk()
            ->assertTableActionVisible('post', $entry);

        $role->revokePermissionTo(FinancePermission::JOURNAL_ENTRIES_POST);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user->fresh());

        Livewire::test(ListJournalEntries::class)
            ->assertOk()
            ->assertTableActionHidden('post', $entry);
    }

    public function test_report_pages_are_forbidden_without_view_reports(): void
    {
        // The granular permission is what the page gate now checks; revoking the
        // pre-split flat name no longer removes access because the role also
        // holds `finance.reports.read` directly.
        Role::findByName('finance', 'web')->revokePermissionTo(FinancePermission::REPORTS_READ);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user->fresh());

        // The user keeps panel entry, so the 403 must come from the page gate.
        $this->assertFalse(AdminDashboard::canAccess());

        $this->get(TrialBalance::getUrl())->assertForbidden();
    }

    public function test_finance_role_can_enter_the_shared_panel(): void
    {
        $this->actingAs($this->user);

        $this->assertTrue($this->user->canAccessPanel(Filament::getPanel('admin')));
        $this->get(ListChartOfAccounts::getUrl())->assertOk();
    }

    public function test_panel_entry_is_denied_without_finance_permissions(): void
    {
        $plain = User::factory()->create();
        $plain->assignRole(Role::findOrCreate('staff', 'web'));

        $this->actingAs($plain);

        $this->assertFalse($plain->canAccessPanel(Filament::getPanel('admin')));

        // No panel access at all, so the panel middleware bounces them to their
        // own landing page rather than serving a bare 403.
        $this->get(ListChartOfAccounts::getUrl())
            ->assertRedirect(route(LandingPage::routeFor($plain)));
    }

    public function test_executive_dashboard_is_not_exposed_to_finance_staff(): void
    {
        $this->actingAs($this->user);

        $this->assertFalse(AdminDashboard::canAccess());
        $this->get(AdminDashboard::getUrl())->assertForbidden();

        // The permission that is documented to unlock the dashboard still does.
        $this->user->givePermissionTo(
            Permission::findOrCreate('access_admin_dashboard', 'web')
        );
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue(AdminDashboard::canAccess());

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $this->actingAs($admin);

        $this->assertTrue(AdminDashboard::canAccess());
    }

    public function test_dashboard_hides_links_the_user_cannot_use(): void
    {
        // Strip the granular grants too: revoking the pre-split flat names is no
        // longer enough, because the junior role holds the granular names
        // directly and those are what the dashboard now checks.
        $role = Role::findByName('finance', 'web');
        $role->revokePermissionTo([
            FinancePermission::LEDGER_READ,
            FinancePermission::JOURNAL_ENTRIES_READ,
            FinancePermission::REPORTS_READ,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user->fresh());

        // The user still reaches the dashboard via the chart-of-accounts permission.
        Livewire::test(FinanceDashboard::class)
            ->assertOk()
            ->assertSee('Chart of Accounts')
            ->assertDontSee('Recent Journal Entries')
            ->assertDontSee('Trial Balance');
    }

    public function test_finance_navigation_is_hidden_without_any_finance_permission(): void
    {
        $plain = User::factory()->create();
        $plain->assignRole(Role::findOrCreate('staff', 'web'));

        $this->actingAs($plain);

        $this->assertFalse(ChartOfAccountResource::canAccess());
        $this->assertFalse(JournalEntryResource::canAccess());
        $this->assertFalse(FinanceDashboard::canAccess());
    }
}
