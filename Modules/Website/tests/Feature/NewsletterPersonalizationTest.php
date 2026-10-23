<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Models\Newsletter;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Services\NewsletterSendingService;
use Modules\Website\Support\NewsletterPersonalizer;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NewsletterPersonalizationTest extends TestCase
{
    use DatabaseTransactions;

    private function subscriber(array $attributes = []): NewsletterSubscriber
    {
        return new NewsletterSubscriber(array_merge([
            'name' => 'Jane Mary Doe',
            'email' => 'jane.doe@gmail.com',
            'unsubscribe_token' => 'token-'.uniqid(),
        ], $attributes));
    }

    private function newsletter(array $attributes = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'subject' => 'Our winter offer',
            'preview_text' => null,
            'content' => '<p>Our winter offer is live.</p>',
            'status' => Newsletter::STATUS_DRAFT,
        ], $attributes));
    }

    private function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => RoleEnum::WEBSITE_ADMIN->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_website_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = User::factory()->create([
            'name' => 'Ada Brickspoint',
            'type' => 'staff',
            'status' => 'active',
        ]);
        $admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);

        return $admin;
    }

    public function test_personalizer_replaces_all_supported_tags()
    {
        $subscriber = $this->subscriber();

        $this->assertSame('Jane Mary Doe', NewsletterPersonalizer::apply('{{name}}', $subscriber));
        $this->assertSame('Jane', NewsletterPersonalizer::apply('{{ first_name }}', $subscriber));
        $this->assertSame('jane.doe@gmail.com', NewsletterPersonalizer::apply('{{EMAIL}}', $subscriber));
        $this->assertSame(
            'Hi Jane, Jane Mary Doe (jane.doe@gmail.com)',
            NewsletterPersonalizer::apply('Hi {{first_name}}, {{name}} ({{email}})', $subscriber)
        );
        $this->assertSame('No tags here', NewsletterPersonalizer::apply('No tags here', $subscriber));
    }

    public function test_personalizer_falls_back_when_name_is_missing()
    {
        $subscriber = $this->subscriber(['name' => null]);

        $this->assertSame(
            'Valued Subscriber|there',
            NewsletterPersonalizer::apply('{{name}}|{{first_name}}', $subscriber)
        );
    }

    public function test_personalizer_escapes_html_in_body_values_but_not_subjects()
    {
        $subscriber = $this->subscriber(['name' => '<script>alert(1)</script>']);

        $this->assertSame(
            '<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>',
            NewsletterPersonalizer::apply('<p>{{name}}</p>', $subscriber)
        );
        $this->assertSame(
            '<script>alert(1)</script>',
            NewsletterPersonalizer::apply('{{name}}', $subscriber, escape: false)
        );
    }

    public function test_mail_personalizes_subject_preview_and_body()
    {
        $newsletter = $this->newsletter([
            'subject' => 'Hello {{first_name}}',
            'preview_text' => '{{name}}, your offer is inside',
            'content' => '<p>Hi {{first_name}}, welcome aboard.</p>',
        ]);

        $mail = new NewsletterMail($newsletter, $this->subscriber());

        $this->assertSame('Hello Jane', $mail->envelope()->subject);

        $html = $mail->render();
        $this->assertStringContainsString('Hi Jane, welcome aboard.', $html);
        $this->assertStringContainsString('Jane Mary Doe, your offer is inside', $html);
    }

    public function test_mail_omits_automatic_greeting_when_body_uses_a_name_tag()
    {
        $newsletter = $this->newsletter([
            'content' => '<p>Hi {{first_name}}, thanks for staying with us.</p>',
        ]);

        $html = (new NewsletterMail($newsletter, $this->subscriber()))->render();

        $this->assertStringContainsString('Hi Jane, thanks for staying with us.', $html);
        $this->assertStringNotContainsString('Dear <strong>', $html);
    }

    public function test_mail_keeps_automatic_greeting_when_body_has_no_name_tag()
    {
        $newsletter = $this->newsletter();

        $html = (new NewsletterMail($newsletter, $this->subscriber()))->render();

        $this->assertStringContainsString('Dear <strong>Jane Mary Doe</strong>', $html);
    }

    public function test_nameless_subscriber_falls_back_in_greeting_tags_and_subject()
    {
        $subscriber = $this->subscriber(['name' => null]);

        $plain = $this->newsletter(['subject' => 'Hello {{first_name}}']);
        $html = (new NewsletterMail($plain, $subscriber))->render();

        $this->assertStringContainsString('Dear Valued Subscriber', $html);
        $this->assertSame('Hello there', (new NewsletterMail($plain, $subscriber))->envelope()->subject);

        $tagged = $this->newsletter([
            'content' => '<p>Hi {{first_name}}, thanks for reading {{name}}.</p>',
        ]);
        $html = (new NewsletterMail($tagged, $subscriber))->render();

        $this->assertStringContainsString('Hi there, thanks for reading Valued Subscriber.', $html);
        $this->assertStringNotContainsString('Dear', $html);
    }

    public function test_whitespace_only_name_falls_back_to_valued_subscriber()
    {
        $html = (new NewsletterMail($this->newsletter(), $this->subscriber(['name' => '   '])))->render();

        $this->assertStringContainsString('Dear Valued Subscriber', $html);
        $this->assertStringNotContainsString('Dear <strong>', $html);
    }

    public function test_bulk_send_delivers_to_nameless_subscribers()
    {
        Mail::fake();

        NewsletterSubscriber::create([
            'email' => 'no.name.one@gmail.com',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        NewsletterSubscriber::create([
            'email' => 'no.name.two@gmail.com',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $newsletter = $this->newsletter([
            'subject' => 'Hello {{first_name}}',
            'content' => '<p>Hi {{first_name}}, our winter offer is live.</p>',
        ]);

        $result = app(NewsletterSendingService::class)->send($newsletter);

        $this->assertSame(['recipients' => 2, 'sent' => 2, 'failed' => 0], $result);
        $this->assertSame(Newsletter::STATUS_SENT, $newsletter->fresh()->status);

        Mail::assertSent(NewsletterMail::class, 2);
        Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) {
            return $mail->hasTo('no.name.one@gmail.com')
                && $mail->envelope()->subject === 'Hello there'
                && str_contains($mail->render(), 'Hi there, our winter offer is live.');
        });
    }

    public function test_preview_email_shows_personalized_content_using_the_admin_name()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = $this->makeAdmin();

        $newsletter = $this->newsletter([
            'content' => '<p>Hi {{name}}, this is a preview.</p>',
        ]);

        $response = $this->actingAs($admin)->get(
            route('website.admin.newsletter.campaigns.preview.email', $newsletter)
        );

        $response->assertOk();
        $response->assertSee('Hi Ada Brickspoint, this is a preview.', false);
        $response->assertDontSee('{{name}}', false);
    }
}
