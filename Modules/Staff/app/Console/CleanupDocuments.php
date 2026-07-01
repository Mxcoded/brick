<?php

namespace Modules\Staff\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Staff\Models\SharedDocument;

class CleanupDocuments extends Command
{
    protected $signature = 'documents:cleanup {--days=7 : Clean up documents older than this many days}';

    protected $description = 'Remove physical files of shared documents older than the retention period, keeping records for reporting';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $documents = SharedDocument::where('created_at', '<', $cutoff)
            ->whereNotNull('file_path')
            ->get();

        $count = 0;

        foreach ($documents as $document) {
            Storage::disk('documents')->delete($document->file_path);

            $document->update(['file_path' => null]);

            $count++;
        }

        $this->info("Cleaned up physical files for {$count} shared document(s) older than {$days} days. Records kept for reporting.");

        return Command::SUCCESS;
    }
}
