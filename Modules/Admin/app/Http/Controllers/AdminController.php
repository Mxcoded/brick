<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Staff\Models\Employee;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin::dashboard');
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

    public function permissions()
    {
        $permissions = Permission::all();
        return view('admin::permissions.index', compact('permissions'));
    }

    public function createPermission(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $request->name]);
        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

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
            'role' => 'required|exists:roles,name'
        ]);
        $user = User::find($request->user_id);
        $user->syncRoles([$request->role]); // Replace existing roles with the new one
        return redirect()->route('admin.users.index')->with('success', 'Role assigned successfully.');
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        // Create the user
        $user = User::create([
            'name' => Employee::find($request->employee_id)->name, // Assuming employee has a name
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Link the user to the employee
        Employee::where('id', $request->employee_id)->update(['user_id' => $user->id]);

        // Assign the selected role
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'User account created and linked to employee successfully.');
    }
}
