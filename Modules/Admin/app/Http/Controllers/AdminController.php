<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
}
