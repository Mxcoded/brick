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

        // 2. Staff Dashboard (HR, Receptionist, Managers go here)
        if ($user->can('access_staff_dashboard')) {
            return redirect()->route('staff.dashboard');
        }

        // 3. Front Desk Specific (If you have a separate dashboard for them)
        if ($user->can('access_frontdesk_dashboard')) {
            return redirect()->route('frontdesk.dashboard'); // Or whatever the route is
        }

        // 4. Default: Guest Dashboard
        return redirect()->route('website.guest.dashboard');
    }
}
