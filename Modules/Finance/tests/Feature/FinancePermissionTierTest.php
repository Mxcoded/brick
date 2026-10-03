<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Filament\Pages\FinanceDashboard;
use Modules\Finance\Filament\Pages\ProfitLoss;
use Modules\Finance\Filament\Pages\TrialBalance;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use Modules\Finance\Support\FinancePermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Segregation of duties for the Finance module.
 *
 * The point of the CRUD split is that "can see the ledger" and "can move money
 * in the ledger" are separate grants, so these tests pin the boundaries
 * explicitly rather than only proving the seeder ran.
 */
class FinancePermissionTierTest extends TestCase
{
    use DatabaseTransactions;

    private function accountant(string $roleName): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    public function test_seeder_creates_every_granular_permission(): void
    {
        $this->seed(FinancePermissionSeeder::class);

        foreach (FinancePermission::all() as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_legacy_flat_permissions_are_still_created_for_backward_compatibility(): void
    {
        $this->seed(FinancePermissionSeeder::class);

        foreach (['finance.view_coa', 'finance.manage_coa', 'finance.create_journal', 'finance.post_journal', 'finance.view_ledger', 'finance.view_reports', 'finance.manage_periods'] as $legacy) {
            $this->assertDatabaseHas('permissions', ['name' => $legacy, 'guard_name' => 'web']);
        }
    }

    public function test_junior_accountant_can_read_and_export_but_cannot_move_money(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $junior = $this->accountant(FinancePermission::ROLE_JUNIOR);

        // Read and export are granted.
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_READ));
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::LEDGER_READ));
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::REPORTS_READ));
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::REPORTS_EXPORT));
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::CHART_OF_ACCOUNTS_READ));

        // Anything that mutates the ledger is not.
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_CREATE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_UPDATE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_DELETE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_POST));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::JOURNAL_ENTRIES_REVERSE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::PERIODS_UPDATE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::CHART_OF_ACCOUNTS_CREATE));
        $this->assertFalse(FinancePermission::can($junior, FinancePermission::FISCAL_YEARS_CREATE));
    }

    public function test_senior_accountant_tier_is_a_strict_superset_of_the_junior_tier(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $senior = $this->accountant(FinancePermission::ROLE_SENIOR);

        foreach (FinancePermission::forRole(FinancePermission::ROLE_JUNIOR) as $permission) {
            $this->assertTrue(
                FinancePermission::can($senior, $permission),
                "Senior accountant should also hold {$permission}."
            );
        }

        $this->assertTrue(FinancePermission::can($senior, FinancePermission::JOURNAL_ENTRIES_CREATE));
        $this->assertTrue(FinancePermission::can($senior, FinancePermission::JOURNAL_ENTRIES_POST));
        $this->assertTrue(FinancePermission::can($senior, FinancePermission::JOURNAL_ENTRIES_REVERSE));
        $this->assertTrue(FinancePermission::can($senior, FinancePermission::PERIODS_UPDATE));
    }

    public function test_auditor_is_read_only(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $auditor = $this->accountant(FinancePermission::ROLE_AUDITOR);

        $this->assertTrue(FinancePermission::can($auditor, FinancePermission::JOURNAL_ENTRIES_READ));
        $this->assertTrue(FinancePermission::can($auditor, FinancePermission::LEDGER_READ));

        foreach (FinancePermission::SENIOR_ONLY as $permission) {
            $this->assertFalse(
                FinancePermission::can($auditor, $permission),
                "Auditor must not hold {$permission}."
            );
        }
    }

    public function test_junior_accountant_cannot_open_the_journal_create_page(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $junior = $this->accountant(FinancePermission::ROLE_JUNIOR);

        // Reading the journal list is allowed; drafting a new entry is not.
        $this->actingAs($junior)
            ->get(JournalEntryResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($junior)
            ->get(JournalEntryResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_senior_accountant_can_open_the_journal_create_page(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $senior = $this->accountant(FinancePermission::ROLE_SENIOR);

        $this->actingAs($senior)
            ->get(JournalEntryResource::getUrl('create'))
            ->assertOk();
    }

    public function test_junior_accountant_can_still_read_reports_and_dashboard(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $junior = $this->accountant(FinancePermission::ROLE_JUNIOR);

        $this->actingAs($junior);

        $this->assertTrue(FinancePermission::allows(FinancePermission::REPORTS_READ));

        $this->get(TrialBalance::getUrl())->assertOk();
        $this->get(ProfitLoss::getUrl())->assertOk();
        $this->get(FinanceDashboard::getUrl())->assertOk();
    }

    public function test_legacy_permission_still_satisfies_the_granular_check(): void
    {
        $this->seed(FinancePermissionSeeder::class);

        // A user provisioned before the split holds only `finance.view_reports`.
        $legacyUser = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $legacyUser->givePermissionTo('finance.view_reports');

        $this->assertTrue(
            FinancePermission::can($legacyUser, FinancePermission::REPORTS_READ),
            'A pre-split grant must keep working after the taxonomy change.'
        );
    }

    public function test_a_legacy_read_grant_does_not_silently_grant_export(): void
    {
        $this->seed(FinancePermissionSeeder::class);

        // Exporting never existed under the old names, so a pre-split read grant
        // must not quietly become the ability to download the whole ledger.
        $legacyUser = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $legacyUser->givePermissionTo([
            'finance.view_reports',
            'finance.view_ledger',
            'finance.view_coa',
            'finance.manage_coa',
            'finance.post_journal',
            'finance.create_journal',
        ]);

        $this->assertTrue(FinancePermission::can($legacyUser, FinancePermission::REPORTS_READ));
        $this->assertTrue(FinancePermission::can($legacyUser, FinancePermission::LEDGER_READ));
        $this->assertTrue(FinancePermission::can($legacyUser, FinancePermission::CHART_OF_ACCOUNTS_READ));
        $this->assertTrue(FinancePermission::can($legacyUser, FinancePermission::JOURNAL_ENTRIES_POST));

        foreach (FinancePermission::all() as $exportable) {
            if (! str_ends_with($exportable, '.export')) {
                continue;
            }

            $this->assertFalse(
                FinancePermission::can($legacyUser, $exportable),
                "[{$exportable}] must be granted explicitly, not inherited from a legacy read grant."
            );
        }
    }

    public function test_no_permission_is_granted_outside_its_own_resource(): void
    {
        // Guards against an alias being mapped too generously: holding the
        // ledger read grant must not imply the ability to post.
        $this->assertSame(
            [FinancePermission::LEDGER_READ, 'finance.view_ledger'],
            FinancePermission::equivalentsFor(FinancePermission::LEDGER_READ)
        );
        $this->assertNotContains(
            FinancePermission::JOURNAL_ENTRIES_POST,
            FinancePermission::equivalentsFor(FinancePermission::JOURNAL_ENTRIES_READ)
        );
    }

    public function test_dashboard_permission_is_reported_for_a_junior_accountant(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $junior = $this->accountant(FinancePermission::ROLE_JUNIOR);

        $data = FinanceDashboard::getUrl();

        $this->assertNotEmpty($data);
        $this->assertTrue(FinancePermission::can($junior, FinancePermission::DASHBOARD_ACCESS));
    }

    public function test_journal_list_page_is_reachable_for_a_reader(): void
    {
        $this->seed(FinancePermissionSeeder::class);
        $junior = $this->accountant(FinancePermission::ROLE_JUNIOR);

        $this->actingAs($junior)
            ->get(ListJournalEntries::getUrl())
            ->assertOk();
    }
}
