<?php

namespace Modules\Website\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterDeliveryLog;
use Modules\Website\Models\NewsletterSubscriber;

class NewsletterSendingService
{
    /**
     * Send a newsletter campaign to every active subscriber.
     *
     * Shared by the admin "Send now" action and the scheduler command
     * (website:send-due-newsletters) so both paths behave identically.
     *
     * @return array{recipients: int, sent: int, failed: int}
     */
    public function send(Newsletter $newsletter): array
    {
        $subscribers = NewsletterSubscriber::active()->get();
        $recipientCount = $subscribers->count();

        if ($recipientCount === 0) {
            Log::warning('Newsletter has no active subscribers, skipping send', [
                'newsletter_id' => $newsletter->id,
            ]);

            return ['recipients' => 0, 'sent' => 0, 'failed' => 0];
        }

        $newsletter->markAsSending($recipientCount);

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            $deliveryLog = NewsletterDeliveryLog::create([
                'newsletter_id' => $newsletter->id,
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'status' => NewsletterDeliveryLog::STATUS_PENDING,
            ]);

            try {
                $subscriber->ensureUnsubscribeToken();

                Mail::to($subscriber->email)->send(
                    new NewsletterMail($newsletter, $subscriber)
                );

                $deliveryLog->markAsSent();
                $sent++;
            } catch (\Throwable $e) {
                $deliveryLog->markAsFailed($e->getMessage());
                $failed++;

                Log::error('Newsletter delivery failed', [
                    'newsletter_id' => $newsletter->id,
                    'subscriber_email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $newsletter->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            // A campaign where nothing was delivered is a failed send, not a
            // successful one — it must not read as green "Sent" in the UI.
            'status' => $sent > 0 ? Newsletter::STATUS_SENT : Newsletter::STATUS_FAILED,
            'sent_at' => $sent > 0 ? now() : $newsletter->sent_at,
        ]);

        Log::info('Newsletter send finished', [
            'newsletter_id' => $newsletter->id,
            'recipients' => $recipientCount,
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return ['recipients' => $recipientCount, 'sent' => $sent, 'failed' => $failed];
    }
}
