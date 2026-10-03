<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Support\FinancePermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy flat names are recreated too: they are still accepted as
        // aliases by FinancePermission::can(), so leaving them in place means an
        // existing database keeps working through the rollout.
        foreach (FinancePermission::allIncludingLegacy() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $tiers = [
            FinancePermission::ROLE_JUNIOR,
            FinancePermission::ROLE_SENIOR,
            FinancePermission::ROLE_AUDITOR,
        ];

        // Legacy flat names are revoked from the Finance roles before the
        // granular set is granted. Aliases keep resolving for individual users
        // who were granted a legacy permission directly, but if a role kept
        // `finance.create_journal` the alias would resolve straight back to
        // journal create and the junior tier would not actually be restricted.
        // The role presets are therefore the authority for role membership.
        $legacyNames = FinancePermission::legacyNames();

        foreach ($tiers as $tier) {
            $role = Role::findOrCreate($tier, 'web');

            $role->revokePermissionTo($legacyNames);

            // Additive on purpose: the admin role holds the full set via
            // RoleSeeder and a role may carry extra permissions granted outside
            // this seeder. syncPermissions() would strip those, which is exactly
            // the bug that locked accountants out of the ledger before.
            $role->givePermissionTo(FinancePermission::forRole($tier));
        }

        FinancePermission::forgetCache();
    }
}
