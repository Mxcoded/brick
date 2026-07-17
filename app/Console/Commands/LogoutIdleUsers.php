<?php

namespace App\Console\Commands;

use App\Models\UserLoginLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogoutIdleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:logout-idle {--hours=3 : Log out users idle for more than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force-logs-out users whose sessions have been idle for more than the given number of hours (default 3).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        $this->info("Logging out sessions idle since before {$cutoff->toDateTimeString()} ({$hours}h)...");

        $idle = UserLoginLog::where('status', 'success')
            ->whereNull('logged_out_at')
            ->where(function ($query) use ($cutoff) {
                $query->where('last_activity_at', '<', $cutoff)
                    ->orWhere(function ($q) use ($cutoff) {
                        $q->whereNull('last_activity_at')
                            ->where('logged_in_at', '<', $cutoff);
                    });
            })
            ->get();

        if ($idle->isEmpty()) {
            $this->info('No idle sessions found.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($idle as $log) {
            // Destroy the actual Laravel session so the user is truly logged out.
            if ($log->session_id) {
                DB::table('sessions')->where('id', $log->session_id)->delete();
            }

            $log->update(['logged_out_at' => now()]);
            $count++;
        }

        $this->info("Logged out {$count} idle session(s).");
        Log::info("Scheduled Task: Logged out {$count} idle session(s) idle for more than {$hours}h.");

        return self::SUCCESS;
    }
}
