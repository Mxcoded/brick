<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Staff\Models\Employee;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Modules\Banquet\Models\BanquetOrder;
use Nwidart\Modules\Facades\Module;
use Illuminate\Http\RedirectResponse;
use App\Models\UserActivityLog;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $recentUsers = User::latest()->take(5)->get();
        $upcomingEvents = BanquetOrder::upcoming()->take(3)->get();
        $activeModules = collect(Module::all())->filter(fn($m) => $m->isEnabled())->count();
        return view('admin::dashboard', compact(
            'totalUsers', 'totalRoles', 'totalPermissions',
            'recentUsers', 'upcomingEvents', 'activeModules'
        ));
    }

    public function roles()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissionsByModule($permissions);
        return view('admin::roles.index', compact('roles', 'permissions', 'permissionGroups'));
    }

    private function groupPermissionsByModule($permissions)
    {
        $moduleMap = [
            'Dashboard Access' => ['access_admin_dashboard', 'access_frontdesk_dashboard', 'access_staff_dashboard', 'access_restaurant_dashboard', 'access_gym_dashboard', 'access_inventory_dashboard', 'access_maintenance_dashboard', 'access_tasks_dashboard', 'access_banquet_dashboard', 'access_website_dashboard'],
        ];

        $groups = [];
        $uncategorized = [];

        foreach ($permissions as $perm) {
            $matched = false;

            // Check legacy underscore permissions first
            if (!str_contains($perm->name, '.')) {
                if (str_starts_with($perm->name, 'access_')) {
                    $groups['Dashboard Access'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'manage_users') || str_starts_with($perm->name, 'manage_roles') || str_starts_with($perm->name, 'manage_permissions') || str_starts_with($perm->name, 'manage_settings')) {
                    $groups['Admin'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'check_in') || str_starts_with($perm->name, 'check_out') || str_starts_with($perm->name, 'manage_rooms')) {
                    $groups['Front Desk'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'view_employees') || str_starts_with($perm->name, 'manage_employees')) {
                    $groups['HR & Staff'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'approve_leaves')) {
                    $groups['HR & Staff'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'view_inventory') || str_starts_with($perm->name, 'adjust_stock') || str_starts_with($perm->name, 'manage_suppliers')) {
                    $groups['Inventory'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'take_orders') || str_starts_with($perm->name, 'manage_menu')) {
                    $groups['Restaurant'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'view_tasks') || str_starts_with($perm->name, 'assign_tasks')) {
                    $groups['Tasks'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'manage_banquet')) {
                    $groups['Banquet'][] = $perm;
                    continue;
                }
                if (str_starts_with($perm->name, 'log_maintenance')) {
                    $groups['Maintenance'][] = $perm;
                    continue;
                }
            }

            // Dot-notation CRUD permissions
            if (str_contains($perm->name, '.')) {
                $prefix = explode('.', $perm->name)[0];
                switch ($prefix) {
                    case 'users': case 'roles': case 'permissions': case 'settings':
                        $group = 'Admin'; break;
                    case 'guests':
                        $group = 'Front Desk'; break;
                    case 'employees': case 'leaves':
                        $group = 'HR & Staff'; break;
                    case 'tasks':
                        $group = 'Tasks'; break;
                    case 'inventory': case 'suppliers':
                        $group = 'Inventory'; break;
                    case 'orders': case 'menu':
                        $group = 'Restaurant'; break;
                    case 'banquet':
                        $group = 'Banquet'; break;
                    case 'gym':
                        $group = 'Gym'; break;
                    default:
                        $group = 'Other'; break;
                }
                $groups[$group][] = $perm;
                continue;
            }

            $uncategorized[] = $perm;
        }

        if (!empty($uncategorized)) {
            $groups['Other'] = $uncategorized;
        }

        return $groups;
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully with ' . ($request->permissions ? count($request->permissions) : 0) . ' permissions.');
    }

    // public function editRole($id)
    // {
    //     $role = Role::findOrFail($id);
    //     return view('admin::roles.edit', compact('role'));
    // }

    // public function updateRole(Request $request, $id)
    // {
    //     $role = Role::findOrFail($id);
    //     $request->validate([
    //         'name' => 'required|unique:roles,name,' . $role->id,
    //     ]);
    //     $role->update(['name' => $request->name]);
    //     return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    // }
    public function editRole($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissionsByModule($permissions);

        return view('admin::roles.edit', compact('role', 'permissions', 'permissionGroups'));
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array', // Validate permissions array
            'permissions.*' => 'exists:permissions,name'
        ]);

        // Update Name
        $role->update(['name' => $request->name]);

        // Dynamic: Sync the checked permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            // If no permissions checked, remove all
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role and permissions updated successfully.');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
    public function massDestroyRole(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        // Optional: Prevent deleting the Super Admin role if applicable
        // Role::whereIn('id', $request->ids)->where('name', '!=', 'admin')->delete();

        Role::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Selected roles deleted successfully.');
    }
    public function permissions()
    {
        $permissions = Permission::with('roles')->get();
        $roles = Role::all();
        return view('admin::permissions.index', compact('permissions', 'roles'));
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'guard_name' => 'nullable|string',
        ]);
        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);
        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$request->name}' created successfully.");
    }

    public function editPermission($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        $roles = Role::all();
        return view('admin::permissions.edit', compact('permission', 'roles'));
    }

    public function updatePermission(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);
        $permission->update(['name' => $request->name]);
        return redirect()->route('admin.permissions.index')->with('success', "Permission updated to '{$request->name}' successfully.");
    }

    public function updatePermissionRoles(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.permissions.edit', $permission->id)->with('success', 'Role assignments updated successfully.');
    }

    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permission->name}' deleted successfully.");
    }

    public function assignPermissionToRole(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        if ($role->hasPermissionTo($permission)) {
            return redirect()->route('admin.permissions.index')->with('info', "Role '{$role->name}' already has this permission.");
        }

        $role->givePermissionTo($permission);
        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permission->name}' assigned to role '{$role->name}'.");
    }

    public function users(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $users = $query->get();
        $roles = Role::all();
        $totalStaff = User::staff()->count();
        $totalGuests = User::guest()->count();
        $staffRoles = Role::where('name', '!=', 'guest')->pluck('name')->toArray();
        $guestRoles = ['guest'];
        return view('admin::users.index', compact('users', 'roles', 'totalStaff', 'totalGuests', 'staffRoles', 'guestRoles'));
    }

    public function editPassword($id)
    {
        $user = User::findOrFail($id);
        return view('admin::users.password', compact('user'));
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => $request->password]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password updated successfully for {$user->name}.");
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array|max:2',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $allowedRoles = $user->isGuest() ? ['guest'] : Role::where('name', '!=', 'guest')->pluck('name')->toArray();

        foreach ($request->roles as $role) {
            if (!in_array($role, $allowedRoles)) {
                return back()->with('error', "Role '{$role}' is not allowed for {$user->type} accounts.");
            }
        }

        $user->syncRoles($request->roles);

        if ($user->isGuest() && in_array('guest', $request->roles)) {
            $user->update(['type' => 'guest']);
        } elseif ($user->isStaff() || !$user->type) {
            $user->update(['type' => 'staff']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Roles assigned successfully.');
    }

    public function createGuest()
    {
        return view('admin::users.create-guest');
    }

    public function storeGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'guest',
        ]);

        $user->assignRole('guest');

        return redirect()->route('admin.users.index', ['type' => 'guest'])
            ->with('success', "Guest account created for {$user->name}.");
    }

    public function updateType(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:staff,guest',
        ]);

        $user = User::findOrFail($id);
        $newType = $request->type;

        if ($user->type === $newType) {
            return redirect()->route('admin.users.index')->with('info', "User is already a {$newType} account.");
        }

        if ($newType === 'guest') {
            $user->syncRoles(['guest']);
            $user->update(['type' => 'guest']);
            $msg = "{$user->name} changed to guest account with guest role.";
        } else {
            $user->removeRole('guest');
            $user->update(['type' => 'staff']);
            $msg = "{$user->name} changed to staff account. Assign roles via the Roles button.";
        }

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    public function createUserFromEmployee()
    {
        $employees = Employee::doesntHave('user')->get(); // Employees without user accounts
        $roles = Role::all();
        return view('admin::employees.create-user', compact('employees', 'roles'));
    }

    public function storeUserFromEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:8|confirmed',
            'role'        => 'required|exists:roles,name',
        ]);

        try {
            DB::beginTransaction(); // Start the transaction

            // 1. Find the employee first to ensure they exist and get their name
            $employee = Employee::findOrFail($request->employee_id);

            // 2. Create the user
            $user = User::create([
                'name'     => $employee->name ?? $employee->first_name . ' ' . ($employee->last_name ?? ''),
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'type'     => 'staff',
            ]);

            // 3. Link the user to the employee
            $employee->update(['user_id' => $user->id]);

            // 4. Assign the selected role
            $user->assignRole($request->role);

            DB::commit(); // Commit the transaction

            return redirect()->route('admin.users.index')
                ->with('success', 'User account created and linked to employee successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if any step fails

            return back()
                ->with('error', 'Error creating user account: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function modules()
    {
        $allModules = Module::all();
        return view('admin::modules.index', compact('allModules'));
    }

    public function toggleModule($name)
    {
        $module = Module::find($name);
        if (!$module) {
            return back()->with('error', "Module '{$name}' not found.");
        }

        if ($module->isEnabled()) {
            $module->disable();
            return back()->with('success', "Module '{$name}' disabled.");
        } else {
            $module->enable();
            return back()->with('success', "Module '{$name}' enabled.");
        }
    }

    public function activityLogs(Request $request)
    {
        $query = UserActivityLog::with('user')->recent();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(50);
        $actions = UserActivityLog::select('action')->distinct()->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin::activity-logs.index', compact('logs', 'actions', 'users'));
    }
}
