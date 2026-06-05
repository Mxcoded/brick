<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Enums\RoleEnum;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user has been authenticated.
     * We override this method to handle dynamic redirection.
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Priority: Admin Dashboard
        if ($user->can('access_admin_dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Front Desk
        if ($user->can('access_frontdesk_dashboard')) {
            return redirect()->route('frontdesk.dashboard');
        }

        // 3. HR Dashboard (only for users with employee management permissions)
        if ($user->can('access_staff_dashboard') && $user->can('view_employees')) {
            return redirect()->route('staff.dashboard');
        }

        // 4. Regular Staff → personal task list
        if ($user->can('access_tasks_dashboard')) {
            return redirect()->route('tasks.index');
        }

        // 5. Staff with no roles assigned
        if ($user->isStaff()) {
            return redirect()->route('staff.pending');
        }

        // 6. Default: Guest Dashboard
        return redirect()->route('guest.dashboard');
    }
}
