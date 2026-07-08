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

        $expiredCount = 0;
        $orphanCount = 0;

        // 1. Clean up expired documents
        SharedDocument::where('created_at', '<', $cutoff)
            ->whereNotNull('file_path')
            ->chunkById(100, function ($documents) use (&$expiredCount) {
                foreach ($documents as $document) {
                    Storage::disk('documents')->delete($document->file_path);
                    $document->update(['file_path' => null]);
                    $expiredCount++;
                }
            });

        // 2. Fix orphaned records — files deleted manually from disk
        SharedDocument::whereNotNull('file_path')
            ->chunkById(100, function ($documents) use (&$orphanCount) {
                foreach ($documents as $document) {
                    if (! Storage::disk('documents')->exists($document->file_path)) {
                        $document->update(['file_path' => null]);
                        $orphanCount++;
                    }
                }
            });

        $this->line("Expired: {$expiredCount} file(s) cleaned up.");
        if ($orphanCount > 0) {
            $this->warn("Orphaned: {$orphanCount} record(s) had missing files on disk — file_path set to null.");
        }
        $this->info('Done.');

        return Command::SUCCESS;
    }
}
