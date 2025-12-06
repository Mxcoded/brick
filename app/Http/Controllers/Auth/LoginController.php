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
        // 1. Super Admin & Admin -> Admin Dashboard
        if ($user->hasRole([RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value])) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Staff -> Staff Dashboard
        if ($user->hasRole(RoleEnum::STAFF->value)) {
            return redirect()->route('staff.dashboard');
        }

        // 3. Guest (Default) -> Guest Dashboard
        return redirect()->route('guest.dashboard');
    }
}
