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

class AdminController extends Controller
{
    public function index()
    {
        $upcomingEvents = BanquetOrder::upcoming()->take(3)->get();
        return view('admin::dashboard', compact('upcomingEvents'));
    }

    public function roles()
    {
        $roles = Role::all();
        return view('admin::roles.index', compact('roles'));
    }

    public function createRole(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);
        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
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

        // Fetch all permissions
        $permissions = Permission::all();

        // Optional: Group permissions manually or by prefix for better UI
        // Example: group 'access_admin_dashboard' under 'Access' or 'Admin'
        $permissionGroups = $permissions->groupBy(function ($data) {
            // Simple logic: Group by the first word of the permission name (e.g., "access", "manage")
            // Or you can map specific keywords to groups
            $parts = explode('_', $data->name);
            return ucfirst($parts[0]); // Returns "Access", "Manage", "View", etc.
        });

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
    // public function permissions()
    // {
    //     $permissions = Permission::all();
    //     $roles = Role::all();
    //     return view('admin::permissions.index', compact('permissions', 'roles'));
    // }

    // public function createPermission(Request $request)
    // {
    //     $request->validate(['name' => 'required|unique:permissions,name']);
    //     Permission::create(['name' => $request->name]);
    //     return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    // }

    // public function editPermission($id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     $roles = Role::all(); // Fetch all roles for assignment options
    //     return view('admin::permissions.edit', compact('permission', 'roles'));
    // }

    // public function updatePermission(Request $request, $id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     $request->validate([
    //         'name' => 'required|unique:permissions,name,' . $permission->id,
    //     ]);
    //     $permission->update(['name' => $request->name]);
    //     return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    // }

    // public function updatePermissionRoles(Request $request, $id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     $request->validate([
    //         'roles' => 'nullable|array',
    //         'roles.*' => 'exists:roles,id',
    //     ]);

    //     // Sync the selected roles with the permission
    //     $permission->roles()->sync($request->input('roles', []));

    //     return redirect()->route('admin.permissions.edit', $permission->id)->with('success', 'Role assignments updated successfully.');
    // }

    // public function destroyPermission($id)
    // {
    //     $permission = Permission::findOrFail($id);
    //     $permission->delete();
    //     return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    // }
    // public function assignPermissionToRole(Request $request)
    // {
    //     $request->validate([
    //         'permission_id' => 'required|exists:permissions,id',
    //         'role_id' => 'required|exists:roles,id',
    //     ]);

    //     $role = Role::findOrFail($request->role_id);
    //     $permission = Permission::findOrFail($request->permission_id);
    //     $role->givePermissionTo($permission);

    //     return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permission->name}' assigned to role '{$role->name}' successfully.");
    // }

    public function users()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        return view('admin::users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array|max:2',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles($request->roles);

        return redirect()->route('admin.users.index')->with('success', 'Roles assigned successfully.');
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
                'name'     => $employee->first_name . ' ' . $employee->last_name, // Combine names
                'email'    => $request->email,
                'password' => Hash::make($request->password),
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
}
