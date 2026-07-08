<?php

namespace App\Http\Middleware;

use App\Models\UserLoginLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Minimum seconds between activity updates to reduce DB writes.
     */
    protected const THROTTLE_SECONDS = 60;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $this->updateUserActivity(Auth::id());
        }

        return $next($request);
    }

    /**
     * Update the user's last activity timestamp.
     * Throttled to prevent excessive database writes.
     */
    protected function updateUserActivity(int $userId): void
    {
        $cacheKey = 'user_activity_'.$userId;

        // Only update if enough time has passed since last update
        if (Cache::has($cacheKey)) {
            return;
        }

        // Update the most recent active session for this user
        UserLoginLog::where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->where('status', 'success')
            ->latest('logged_in_at')
            ->first()
            ?->update(['last_activity_at' => now()]);

        // Set cache to throttle subsequent updates
        Cache::put($cacheKey, true, self::THROTTLE_SECONDS);
    }
}
