<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\LandingPage;
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

        // 1–6. Priority: admin → front desk → HR → finance → tasks → staff → guest
        return redirect()->route(LandingPage::routeFor($user));
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
                'email' => trans('auth.failed').($remaining > 0 ? " You have {$remaining} attempt(s) remaining." : ''),
            ]);
    }
}
