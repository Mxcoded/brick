<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\RoleEnum;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Actually, we just redirect to the correct Module Dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Admin
        if ($user->can('access_admin_dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        // 2. HR staff
        if ($user->can('access_staff_dashboard') && $user->can('view_employees')) {
            return redirect()->route('staff.dashboard');
        }

        // 3. Front desk
        if ($user->can('access_frontdesk_dashboard')) {
            return redirect()->route('frontdesk.dashboard');
        }

        // 4. Regular staff → tasks
        if ($user->can('access_tasks_dashboard')) {
            return redirect()->route('tasks.index');
        }

        // 5. Staff with no roles assigned
        if ($user->isStaff()) {
            return redirect()->route('staff.pending');
        }

        // 6. Guest (Default)
        return redirect()->route('guest.dashboard');
    }
}
