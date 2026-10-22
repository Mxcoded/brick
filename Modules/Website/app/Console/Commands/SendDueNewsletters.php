<?php

namespace Modules\Website\Console\Commands;

use Illuminate\Console\Command;
use Modules\Website\Models\Newsletter;
use Modules\Website\Services\NewsletterSendingService;

class SendDueNewsletters extends Command
{
    protected $signature = 'website:send-due-newsletters {--dry-run : List due campaigns without sending them}';

    protected $description = 'Send newsletter campaigns whose scheduled time has passed';

    public function handle(NewsletterSendingService $sender): int
    {
        $due = Newsletter::readyToSend()
            ->orderBy('scheduled_at')
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled newsletters due.');

            return self::SUCCESS;
        }

        foreach ($due as $newsletter) {
            if ($this->option('dry-run')) {
                $this->line(sprintf(
                    '[dry-run] #%d "%s" scheduled for %s',
                    $newsletter->id,
                    $newsletter->subject,
                    $newsletter->scheduled_at->format('M d, Y h:i A')
                ));

                continue;
            }

            // Re-read the status: an admin may have cancelled, sent or
            // deleted it while this run was in progress.
            $fresh = $newsletter->fresh();
            if (! $fresh || $fresh->status !== Newsletter::STATUS_SCHEDULED) {
                $this->line("Skipped #{$newsletter->id} (no longer scheduled).");

                continue;
            }

            $result = $sender->send($fresh);

            if ($result['recipients'] === 0) {
                // Left as scheduled so it goes out as soon as subscribers exist.
                $this->warn("Skipped #{$newsletter->id} \"{$newsletter->subject}\": no active subscribers.");

                continue;
            }

            $this->info(sprintf(
                'Sent #%d "%s": %d delivered, %d failed.',
                $newsletter->id,
                $newsletter->subject,
                $result['sent'],
                $result['failed']
            ));
        }

        return self::SUCCESS;
    }
}
