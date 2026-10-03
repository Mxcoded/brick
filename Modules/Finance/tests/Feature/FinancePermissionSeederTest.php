<?php

namespace Modules\Finance\Tests\Feature;

use App\Enums\RoleEnum;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Finance\Support\FinancePermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancePermissionSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_junior_finance_role_receives_read_and_export_permissions(): void
    {
        (new FinancePermissionSeeder)->run();

        $finance = Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web');

        $this->assertTrue($finance->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_READ));
        $this->assertTrue($finance->hasPermissionTo(FinancePermission::LEDGER_READ));
        $this->assertTrue($finance->hasPermissionTo(FinancePermission::REPORTS_READ));
        $this->assertTrue($finance->hasPermissionTo(FinancePermission::REPORTS_EXPORT));
        $this->assertTrue($finance->hasPermissionTo(FinancePermission::FISCAL_YEARS_READ));

        // The whole point of the split: the junior tier cannot move money.
        $this->assertFalse($finance->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_CREATE));
        $this->assertFalse($finance->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_POST));
        $this->assertFalse($finance->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_REVERSE));
        $this->assertFalse($finance->hasPermissionTo(FinancePermission::PERIODS_UPDATE));
    }

    public function test_senior_accountant_role_is_provisioned_with_posting_rights(): void
    {
        (new FinancePermissionSeeder)->run();

        $senior = Role::findOrCreate(FinancePermission::ROLE_SENIOR, 'web');

        $this->assertTrue($senior->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_CREATE));
        $this->assertTrue($senior->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_POST));
        $this->assertTrue($senior->hasPermissionTo(FinancePermission::JOURNAL_ENTRIES_REVERSE));
        $this->assertTrue($senior->hasPermissionTo(FinancePermission::PERIODS_UPDATE));
        $this->assertTrue($senior->hasPermissionTo(FinancePermission::FISCAL_YEARS_CREATE));
    }

    /**
     * A role that was granted the pre-split flat names must not keep write
     * access through the legacy alias, otherwise the junior tier would be
     * unenforceable in an existing database.
     */
    public function test_legacy_write_permissions_are_revoked_from_the_finance_roles(): void
    {
        // Run once so the legacy permissions exist, then re-grant them to
        // simulate a database provisioned before the taxonomy split.
        (new FinancePermissionSeeder)->run();

        $finance = Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web');
        $finance->givePermissionTo(['finance.create_journal', 'finance.post_journal', 'finance.manage_coa']);

        $this->assertTrue($finance->hasPermissionTo('finance.create_journal'));

        (new FinancePermissionSeeder)->run();

        $finance = Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web');

        $this->assertFalse($finance->hasPermissionTo('finance.create_journal'));
        $this->assertFalse($finance->hasPermissionTo('finance.post_journal'));
        $this->assertFalse($finance->hasPermissionTo('finance.manage_coa'));
    }

    /**
     * The core RoleSeeder also provisions the `finance` role. It used to call
     * syncPermissions() with procurement grants only, which silently stripped
     * every finance.* permission and left finance staff unable to open the
     * journal pages (403). It must never remove the module's grants.
     */
    public function test_core_role_seeder_does_not_strip_ledger_permissions(): void
    {
        (new FinancePermissionSeeder)->run();
        $before = Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web')->permissions->pluck('name')->all();

        $this->assertContains(FinancePermission::JOURNAL_ENTRIES_READ, $before);

        (new RoleSeeder)->run();

        $finance = Role::findOrCreate(FinancePermission::ROLE_JUNIOR, 'web');

        foreach ($before as $permission) {
            $this->assertTrue(
                $finance->hasPermissionTo($permission),
                "RoleSeeder stripped [{$permission}] from the finance role."
            );
        }

        $this->assertTrue($finance->hasPermissionTo('procurement.approve_request'));
    }

    public function test_admin_role_receives_every_finance_permission(): void
    {
        (new FinancePermissionSeeder)->run();
        (new RoleSeeder)->run();

        $admin = Role::findOrCreate(RoleEnum::ADMIN->value, 'web');

        foreach (FinancePermission::all() as $permission) {
            $this->assertTrue(
                $admin->hasPermissionTo($permission),
                "Admin is missing the superuser grant [{$permission}]."
            );
        }
    }

    public function test_admin_role_keeps_the_legacy_names_for_forward_compatibility(): void
    {
        (new FinancePermissionSeeder)->run();
        (new RoleSeeder)->run();

        $admin = Role::findOrCreate(RoleEnum::ADMIN->value, 'web');

        foreach (FinancePermission::legacyNames() as $legacy) {
            $this->assertTrue(
                $admin->hasPermissionTo($legacy),
                "Admin should retain the legacy permission [{$legacy}]."
            );
        }
    }
}
