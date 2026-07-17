<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterDeliveryLog;
use Modules\Website\Models\NewsletterSubscriber;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NewsletterFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private NewsletterSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        $role = Role::firstOrCreate(['name' => RoleEnum::WEBSITE_ADMIN->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_website_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);

        $this->actingAs($this->admin);

        $this->subscriber = NewsletterSubscriber::create([
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribe_token' => 'test-valid-token-12345',
        ]);
    }

    // ==========================================
    // PUBLIC SUBSCRIPTION
    // ==========================================

    public function test_guest_can_subscribe_with_valid_email()
    {
        $response = $this->post(route('website.newsletter.subscribe'), [
            'email' => 'new.user@gmail.com',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'new.user@gmail.com',
            'is_active' => true,
        ]);
    }

    public function test_subscribe_with_name_attaches_name()
    {
        $response = $this->post(route('website.newsletter.subscribe'), [
            'name' => 'Jane',
            'email' => 'jane.smith@gmail.com',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jane.smith@gmail.com',
            'name' => 'Jane',
        ]);
    }

    public function test_cannot_subscribe_with_existing_active_email()
    {
        $response = $this->post(route('website.newsletter.subscribe'), [
            'email' => $this->subscriber->email,
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'This email is already subscribed to our newsletter.',
        ]);
    }

    public function test_can_reactivate_inactive_subscription()
    {
        $this->subscriber->update(['is_active' => false, 'unsubscribed_at' => now()]);

        $response = $this->post(route('website.newsletter.subscribe'), [
            'email' => $this->subscriber->email,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $this->subscriber->email,
            'is_active' => true,
            'unsubscribed_at' => null,
        ]);
    }

    public function test_cannot_subscribe_with_invalid_email()
    {
        $response = $this->post(route('website.newsletter.subscribe'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_cannot_subscribe_without_email()
    {
        $response = $this->post(route('website.newsletter.subscribe'), []);

        $response->assertSessionHasErrors('email');
    }

    // ==========================================
    // UNSUBSCRIBE
    // ==========================================

    public function test_subscriber_can_unsubscribe_with_valid_token()
    {
        $response = $this->get(route('website.newsletter.unsubscribe', [
            'token' => $this->subscriber->unsubscribe_token,
        ]));

        $response->assertViewHas('success', true);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $this->subscriber->email,
            'is_active' => false,
        ]);
    }

    public function test_unsubscribe_with_invalid_token_shows_error()
    {
        $response = $this->get(route('website.newsletter.unsubscribe', [
            'token' => 'non-existent-token',
        ]));

        $response->assertViewHas('success', false);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $this->subscriber->email,
            'is_active' => true,
        ]);
    }

    public function test_unsubscribe_when_already_inactive()
    {
        $this->subscriber->update(['is_active' => false, 'unsubscribed_at' => now()]);

        $response = $this->get(route('website.newsletter.unsubscribe', [
            'token' => $this->subscriber->unsubscribe_token,
        ]));

        $response->assertViewHas('success', true);
        $response->assertViewHas('message', 'You have already been unsubscribed.');
    }

    // ==========================================
    // CAMPAIGN MANAGEMENT (Admin)
    // ==========================================

    public function test_admin_can_create_draft_campaign()
    {
        $response = $this->post(route('website.admin.newsletter.campaigns.store'), [
            'subject' => 'Test Newsletter',
            'content' => '<p>Hello World</p>',
            'action' => 'draft',
        ]);

        $response->assertRedirect(route('website.admin.newsletter.campaigns.index'));

        $this->assertDatabaseHas('newsletters', [
            'subject' => 'Test Newsletter',
            'content' => '<p>Hello World</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_create_campaign_with_preview_text()
    {
        $response = $this->post(route('website.admin.newsletter.campaigns.store'), [
            'subject' => 'With Preview',
            'preview_text' => 'Check out our latest offers',
            'content' => '<p>Content</p>',
            'action' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletters', [
            'subject' => 'With Preview',
            'preview_text' => 'Check out our latest offers',
        ]);
    }

    public function test_admin_can_schedule_campaign()
    {
        $response = $this->post(route('website.admin.newsletter.campaigns.store'), [
            'subject' => 'Scheduled Newsletter',
            'content' => '<p>Scheduled content</p>',
            'action' => 'schedule',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('website.admin.newsletter.campaigns.index'));

        $this->assertDatabaseHas('newsletters', [
            'subject' => 'Scheduled Newsletter',
            'status' => Newsletter::STATUS_SCHEDULED,
        ]);
    }

    public function test_admin_can_send_campaign_immediately()
    {
        Mail::fake();

        $response = $this->post(route('website.admin.newsletter.campaigns.store'), [
            'subject' => 'Send Now',
            'content' => '<p>Immediate send</p>',
            'action' => 'send',
        ]);

        $response->assertRedirect();

        $newsletter = Newsletter::where('subject', 'Send Now')->first();
        $this->assertNotNull($newsletter);
        $this->assertEquals(Newsletter::STATUS_SENT, $newsletter->status);
        $this->assertEquals(1, $newsletter->sent_count);

        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo($this->subscriber->email);
        });
    }

    public function test_admin_can_preview_campaign()
    {
        $newsletter = Newsletter::create([
            'subject' => 'Preview Test',
            'content' => '<p>Preview content</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(route('website.admin.newsletter.campaigns.preview', $newsletter));

        $response->assertStatus(200);
        $response->assertSee('<p>Preview content</p>', false);
    }

    public function test_admin_can_duplicate_campaign()
    {
        $newsletter = Newsletter::create([
            'subject' => 'Original',
            'preview_text' => 'Original preview',
            'content' => '<p>Original content</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->post(route('website.admin.newsletter.campaigns.duplicate', $newsletter));

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletters', [
            'subject' => 'Original (Copy)',
        ]);
    }

    // ==========================================
    // DELIVERY TRACKING
    // ==========================================

    public function test_sending_newsletter_creates_delivery_logs()
    {
        Mail::fake();

        $newsletter = Newsletter::create([
            'subject' => 'Delivery Log Test',
            'content' => '<p>Test</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);

        $this->post(route('website.admin.newsletter.campaigns.send', $newsletter));

        $this->assertDatabaseHas('newsletter_delivery_logs', [
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
            'status' => NewsletterDeliveryLog::STATUS_SENT,
        ]);
    }

    public function test_sending_newsletter_updates_counts()
    {
        Mail::fake();

        $newsletter = Newsletter::create([
            'subject' => 'Count Test',
            'content' => '<p>Test</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);

        $this->post(route('website.admin.newsletter.campaigns.send', $newsletter));

        $newsletter->refresh();
        $this->assertEquals(1, $newsletter->sent_count);
        $this->assertEquals(0, $newsletter->failed_count);
        $this->assertEquals(1, $newsletter->recipients_count);
    }

    public function test_delivery_status_page_shows_stats()
    {
        $newsletter = Newsletter::create([
            'subject' => 'Status Page',
            'content' => '<p>Test</p>',
            'status' => Newsletter::STATUS_SENT,
            'recipients_count' => 1,
            'sent_count' => 1,
            'sent_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        NewsletterDeliveryLog::create([
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
            'status' => NewsletterDeliveryLog::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $response = $this->get(route('website.admin.newsletter.campaigns.delivery-status', $newsletter));

        $response->assertStatus(200);
    }

    public function test_delivery_status_api_returns_json()
    {
        $newsletter = Newsletter::create([
            'subject' => 'API Test',
            'content' => '<p>Test</p>',
            'status' => Newsletter::STATUS_SENT,
            'recipients_count' => 1,
            'sent_count' => 1,
            'created_by' => $this->admin->id,
        ]);

        NewsletterDeliveryLog::create([
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
            'status' => NewsletterDeliveryLog::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $response = $this->get(route('website.admin.newsletter.campaigns.delivery-status.api', $newsletter));

        $response->assertJsonStructure([
            'total', 'sent', 'failed', 'pending', 'status', 'sent_count', 'failed_count', 'progress', 'completed',
        ]);
    }

    public function test_only_draft_or_scheduled_campaigns_can_be_edited()
    {
        $sent = Newsletter::create([
            'subject' => 'Already Sent',
            'content' => '<p>Sent</p>',
            'status' => Newsletter::STATUS_SENT,
            'sent_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(route('website.admin.newsletter.campaigns.edit', $sent));
        $response->assertRedirect(route('website.admin.newsletter.campaigns.index'));
    }

    public function test_cannot_send_already_sent_campaign()
    {
        $sent = Newsletter::create([
            'subject' => 'Already Sent',
            'content' => '<p>Sent</p>',
            'status' => Newsletter::STATUS_SENT,
            'sent_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->post(route('website.admin.newsletter.campaigns.send', $sent));
        $response->assertRedirect(route('website.admin.newsletter.campaigns.index'));
        $response->assertSessionHas('error');
    }

    // ==========================================
    // SEND TEST EMAIL
    // ==========================================

    public function test_admin_can_send_test_email()
    {
        Mail::fake();

        $newsletter = Newsletter::create([
            'subject' => 'Test Email',
            'content' => '<p>Test</p>',
            'status' => Newsletter::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->post(route('website.admin.newsletter.campaigns.test', $newsletter), [
            'email' => 'test@example.com',
        ]);

        $response->assertJson(['success' => true]);
        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    // ==========================================
    // SUBSCRIBER MANAGEMENT (Admin)
    // ==========================================

    public function test_admin_can_toggle_subscriber_status()
    {
        $this->assertTrue($this->subscriber->is_active);

        $this->post(route('website.admin.newsletter.subscribers.toggle', $this->subscriber));

        $this->subscriber->refresh();
        $this->assertFalse($this->subscriber->is_active);
        $this->assertNotNull($this->subscriber->unsubscribed_at);
    }

    public function test_admin_can_delete_subscriber()
    {
        $this->delete(route('website.admin.newsletter.subscribers.destroy', $this->subscriber));

        $this->assertDatabaseMissing('newsletter_subscribers', [
            'id' => $this->subscriber->id,
        ]);
    }
}
