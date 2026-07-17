<?php

namespace App\Listeners;

use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            // Find the most recent login without a logout time
            $loginLog = UserLoginLog::where('user_id', $event->user->id)
                ->whereNull('logged_out_at')
                ->where('status', 'success')
                ->latest('logged_in_at')
                ->first();

            if ($loginLog) {
                $loginLog->update([
                    'logged_out_at' => now(),
                ]);
            }
        }
    }
}
