<?php

namespace Modules\Website\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Emails\NewsletterMail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Newsletter $newsletter,
        public NewsletterSubscriber $subscriber
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if subscriber is no longer active
        if (!$this->subscriber->is_active) {
            Log::info('Skipping inactive subscriber', [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_id' => $this->subscriber->id,
            ]);
            return;
        }

        // Ensure subscriber has an unsubscribe token
        if (!$this->subscriber->unsubscribe_token) {
            $this->subscriber->update([
                'unsubscribe_token' => bin2hex(random_bytes(32)),
            ]);
            $this->subscriber->refresh();
        }

        try {
            Mail::to($this->subscriber->email)->send(
                new NewsletterMail($this->newsletter, $this->subscriber)
            );

            // Increment sent count
            $this->newsletter->incrementSentCount();

            Log::info('Newsletter sent successfully', [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_email' => $this->subscriber->email,
            ]);

        } catch (\Exception $e) {
            // Increment failed count
            $this->newsletter->incrementFailedCount();

            Log::error('Failed to send newsletter', [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_email' => $this->subscriber->email,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Newsletter job permanently failed', [
            'newsletter_id' => $this->newsletter->id,
            'subscriber_email' => $this->subscriber->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
