<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterDeliveryLog;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Services\NewsletterSendingService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScheduledNewsletterTest extends TestCase
{
    use DatabaseTransactions;

    private NewsletterSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        $this->subscriber = NewsletterSubscriber::create([
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribe_token' => 'scheduled-test-token-12345',
        ]);
    }

    private function makeScheduledNewsletter(string $subject, string $when): Newsletter
    {
        return Newsletter::create([
            'subject' => $subject,
            'content' => '<p>Body</p>',
            'status' => Newsletter::STATUS_SCHEDULED,
            'scheduled_at' => $when,
        ]);
    }

    #[Test]
    public function command_is_registered_to_run_every_minute(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'website:send-due-newsletters'));

        $this->assertNotNull($event, 'The due-newsletters command must be on the scheduler.');
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping, 'Overlapping runs must be prevented.');
    }

    #[Test]
    public function command_sends_campaign_once_it_is_past_due(): void
    {
        Mail::fake();

        $newsletter = $this->makeScheduledNewsletter('Due Now', now()->subMinutes(5)->format('Y-m-d H:i:s'));

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_SENT, $newsletter->status);
        $this->assertSame(1, $newsletter->recipients_count);
        $this->assertSame(1, $newsletter->sent_count);
        $this->assertSame(0, $newsletter->failed_count);
        $this->assertNotNull($newsletter->sent_at);

        Mail::assertSentTimes(NewsletterMail::class, 1);

        $this->assertDatabaseHas('newsletter_delivery_logs', [
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
            'status' => NewsletterDeliveryLog::STATUS_SENT,
        ]);
    }

    #[Test]
    public function command_leaves_future_campaign_untouched(): void
    {
        Mail::fake();

        $newsletter = $this->makeScheduledNewsletter('Later Today', now()->addHour()->format('Y-m-d H:i:s'));

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_SCHEDULED, $newsletter->status);
        $this->assertNull($newsletter->sent_at);

        Mail::assertNothingSent();
    }

    #[Test]
    public function command_does_not_resend_a_campaign_that_was_already_sent(): void
    {
        Mail::fake();

        Newsletter::create([
            'subject' => 'Already Sent',
            'content' => '<p>Body</p>',
            'status' => Newsletter::STATUS_SENT,
            'scheduled_at' => now()->subDay(),
            'sent_at' => now()->subDay(),
        ]);

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[Test]
    public function command_does_not_send_a_draft_campaign(): void
    {
        Mail::fake();

        Newsletter::create([
            'subject' => 'Draft',
            'content' => '<p>Body</p>',
            'status' => Newsletter::STATUS_DRAFT,
        ]);

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[Test]
    public function command_keeps_campaign_scheduled_when_there_are_no_active_subscribers(): void
    {
        Mail::fake();

        $newsletter = $this->makeScheduledNewsletter('Nobody Listening', now()->subMinutes(5)->format('Y-m-d H:i:s'));
        $this->subscriber->update(['is_active' => false]);

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        // Still scheduled: it should go out once subscribers are active again.
        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_SCHEDULED, $newsletter->status);

        Mail::assertNothingSent();
    }

    #[Test]
    public function dry_run_reports_due_campaign_without_sending(): void
    {
        Mail::fake();

        $newsletter = $this->makeScheduledNewsletter('Dry Run', now()->subMinutes(5)->format('Y-m-d H:i:s'));

        $this->artisan('website:send-due-newsletters --dry-run')
            ->expectsOutputToContain('Dry Run')
            ->assertExitCode(0);

        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_SCHEDULED, $newsletter->status);
        Mail::assertNothingSent();
    }

    #[Test]
    public function campaign_is_marked_failed_when_every_delivery_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP connection refused'));

        $newsletter = $this->makeScheduledNewsletter('Bad SMTP', now()->subMinutes(5)->format('Y-m-d H:i:s'));

        $this->artisan('website:send-due-newsletters')->assertExitCode(0);

        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_FAILED, $newsletter->status);
        $this->assertSame(0, $newsletter->sent_count);
        $this->assertSame(1, $newsletter->failed_count);

        $this->assertDatabaseHas('newsletter_delivery_logs', [
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $this->subscriber->id,
            'status' => NewsletterDeliveryLog::STATUS_FAILED,
        ]);
    }

    #[Test]
    public function immediate_send_from_admin_still_marks_campaign_sent(): void
    {
        Mail::fake();

        $newsletter = $this->makeScheduledNewsletter('Send Now Copy', now()->addDay()->format('Y-m-d H:i:s'));

        $result = app(NewsletterSendingService::class)->send($newsletter);

        $this->assertSame(['recipients' => 1, 'sent' => 1, 'failed' => 0], $result);

        $newsletter->refresh();
        $this->assertSame(Newsletter::STATUS_SENT, $newsletter->status);
        $this->assertSame(1, $newsletter->sent_count);
    }
}
