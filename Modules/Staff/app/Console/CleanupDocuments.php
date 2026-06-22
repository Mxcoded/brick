<?php

namespace Modules\Staff\Console;

use Illuminate\Console\Command;
use Modules\Staff\Models\SharedDocument;

class CleanupDocuments extends Command
{
    protected $signature = 'documents:cleanup {--days=7 : Delete documents older than this many days}';
    protected $description = 'Delete shared documents older than the specified retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = SharedDocument::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$count} shared document(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}
