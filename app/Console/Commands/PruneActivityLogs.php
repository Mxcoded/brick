<?php

namespace App\Console\Commands;

use App\Models\UserActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-logs:prune {--days=90 : Delete activity logs older than this many days (recommended audit retention)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes user activity logs older than the given number of days (default 90 - recommended audit retention).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Pruning activity logs created before {$cutoff->toDateTimeString()} ({$days} days old)...");

        $deleted = UserActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} activity log entr".($deleted === 1 ? 'y' : 'ies').'.');

        Log::info("Scheduled Task: Pruned {$deleted} activity logs older than {$days} days.");

        // Don't log this housekeeping action itself to avoid infinite growth.
        return self::SUCCESS;
    }
}
