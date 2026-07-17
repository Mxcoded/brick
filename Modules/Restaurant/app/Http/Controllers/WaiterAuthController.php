<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class WaiterAuthController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function redirectTo()
    {
        return route('restaurant.waiter.dashboard');
    }

    public function showLoginForm()
    {
        return view('restaurant::waiter.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $reason = $user->suspension_reason
                ? "Reason: {$user->suspension_reason}"
                : 'Please contact the administrator.';

            $statusLabel = $user->status === 'suspended' ? 'suspended' : 'deactivated';

            return redirect()->route('restaurant.waiter.login')
                ->with('account_error', true)
                ->with('account_status', $statusLabel)
                ->with('account_reason', $reason)
                ->with('account_name', $user->name);
        }

        return redirect()->intended($this->redirectTo());
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('restaurant.waiter.login');
    }
}
