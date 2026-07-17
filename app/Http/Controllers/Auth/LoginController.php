<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    use AuthenticatesUsers, ThrottlesLogins;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function decayMinutes()
    {
        return 15;
    }

    public function showLoginForm()
    {
        $remaining = $this->maxAttempts();
        $retryAfter = 0;

        $email = old('email', request()->input('email'));
        if ($email) {
            $request = request();
            $request->merge(['email' => $email]);
            $key = $this->throttleKey($request);

            if (RateLimiter::tooManyAttempts($key, $this->maxAttempts())) {
                $remaining = 0;
                $retryAfter = RateLimiter::availableIn($key);
            } else {
                $remaining = max(0, $this->maxAttempts() - RateLimiter::attempts($key));
            }
        }

        return view('auth.login', compact('remaining', 'retryAfter'));
    }

    protected function authenticated(Request $request, $user)
    {
        // Check if account is suspended or deactivated
        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $reason = $user->suspension_reason
                ? "Reason: {$user->suspension_reason}"
                : 'Please contact the administrator.';

            $statusLabel = $user->status === 'suspended' ? 'suspended' : 'deactivated';

            return redirect()->route('login')
                ->with('account_error', true)
                ->with('account_status', $statusLabel)
                ->with('account_reason', $reason)
                ->with('account_name', $user->name);
        }

        // Clear old sessions for this user (enforce single active session)
        \DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        // 1. Priority: Admin Dashboard
        if ($user->can('access_admin_dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Front Desk
        if ($user->can('access_frontdesk_dashboard')) {
            return redirect()->route('frontdesk.dashboard');
        }

        // 3. HR Dashboard (only for users with employee management permissions)
        if ($user->can('access_staff_dashboard') && $user->can('employees.read')) {
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

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $key = $this->throttleKey($request);
        $max = $this->maxAttempts();

        $remaining = max(0, $max - RateLimiter::attempts($key));

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => trans('auth.failed') . ($remaining > 0 ? " You have {$remaining} attempt(s) remaining." : ''),
            ]);
    }
}
