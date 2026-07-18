<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinancePermissionSeeder extends Seeder
{
    protected array $permissions = [
        'finance.view_coa',
        'finance.manage_coa',
        'finance.post_journal',
        'finance.view_ledger',
        'finance.view_reports',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::findOrCreate('finance', 'web');
        $role->givePermissionTo($this->permissions);
    }
}
