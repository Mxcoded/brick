<?php

namespace Modules\Inventory\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProcurementRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['line_manager', 'staff', 'purchaser', 'gm', 'finance', 'auditor', 'ggm'];

        $permissions = [
            'procurement.create_request',
            'procurement.view_own_requests',
            'procurement.view_all_requests',
            'procurement.review_request',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.flag_request',
            'procurement.attach_invoice',
            'procurement.audit_request',
            'procurement.convert_to_po',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName);
        }

        Role::findByName('line_manager')->givePermissionTo([
            'procurement.create_request',
            'procurement.view_own_requests',
        ]);

        Role::findByName('staff')->givePermissionTo([
            'procurement.create_request',
            'procurement.view_own_requests',
        ]);

        Role::findByName('purchaser')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.review_request',
            'procurement.reject_request',
            'procurement.flag_request',
            'procurement.attach_invoice',
        ]);

        Role::findByName('gm')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.flag_request',
        ]);

        Role::findByName('finance')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
        ]);

        Role::findByName('auditor')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.audit_request',
            'procurement.reject_request',
        ]);

        Role::findByName('ggm')->givePermissionTo([
            'procurement.view_all_requests',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.convert_to_po',
        ]);

        $admin = User::where('email', 'like', '%admin%')->orWhere('id', 1)->first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
