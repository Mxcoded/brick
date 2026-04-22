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
use Modules\Website\Models\NewsletterDeliveryLog;
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
    public int $backoff = 10;

    /**
     * The queue connection that should handle the job.
     * Use 'sync' for immediate processing.
     */
    public $connection = 'sync';

    /**
     * The delivery log ID for tracking.
     */
    public ?int $deliveryLogId = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Newsletter $newsletter,
        public NewsletterSubscriber $subscriber,
        ?int $deliveryLogId = null
    ) {
        $this->deliveryLogId = $deliveryLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get or create delivery log
        $deliveryLog = $this->getOrCreateDeliveryLog();

        // Skip if subscriber is no longer active
        if (!$this->subscriber->is_active) {
            Log::info('Skipping inactive subscriber', [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_id' => $this->subscriber->id,
            ]);
            
            // Mark as failed in delivery log
            $deliveryLog->markAsFailed('Subscriber is no longer active');
            return;
        }

        // Ensure subscriber has an unsubscribe token
        $this->subscriber->ensureUnsubscribeToken();

        // Increment attempt count
        $deliveryLog->incrementAttempts();

        try {
            Mail::to($this->subscriber->email)->send(
                new NewsletterMail($this->newsletter, $this->subscriber)
            );

            // Mark as sent in delivery log
            $deliveryLog->markAsSent();

            // Increment sent count on newsletter
            $this->newsletter->incrementSentCount();

            Log::info('Newsletter sent successfully', [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_email' => $this->subscriber->email,
                'delivery_log_id' => $deliveryLog->id,
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // If this is the last attempt, mark as permanently failed
            if ($this->attempts() >= $this->tries) {
                $deliveryLog->markAsFailed($errorMessage);
                $this->newsletter->incrementFailedCount();

                Log::error('Newsletter delivery permanently failed', [
                    'newsletter_id' => $this->newsletter->id,
                    'subscriber_email' => $this->subscriber->email,
                    'delivery_log_id' => $deliveryLog->id,
                    'error' => $errorMessage,
                    'attempts' => $this->attempts(),
                ]);
            } else {
                Log::warning('Newsletter delivery failed, will retry', [
                    'newsletter_id' => $this->newsletter->id,
                    'subscriber_email' => $this->subscriber->email,
                    'delivery_log_id' => $deliveryLog->id,
                    'error' => $errorMessage,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries,
                ]);
            }

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Get or create the delivery log for this job.
     */
    protected function getOrCreateDeliveryLog(): NewsletterDeliveryLog
    {
        if ($this->deliveryLogId) {
            $log = NewsletterDeliveryLog::find($this->deliveryLogId);
            if ($log) {
                return $log;
            }
        }

        // Find existing or create new
        return NewsletterDeliveryLog::firstOrCreate(
            [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_id' => $this->subscriber->id,
            ],
            [
                'email' => $this->subscriber->email,
                'status' => NewsletterDeliveryLog::STATUS_PENDING,
            ]
        );
    }

    /**
     * Handle a job failure after all retries.
     */
    public function failed(\Throwable $exception): void
    {
        // Ensure the delivery log is marked as failed
        $deliveryLog = $this->getOrCreateDeliveryLog();
        
        if ($deliveryLog->status !== NewsletterDeliveryLog::STATUS_FAILED) {
            $deliveryLog->markAsFailed($exception->getMessage());
            $this->newsletter->incrementFailedCount();
        }

        Log::error('Newsletter job permanently failed after all retries', [
            'newsletter_id' => $this->newsletter->id,
            'subscriber_email' => $this->subscriber->email,
            'delivery_log_id' => $deliveryLog->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
