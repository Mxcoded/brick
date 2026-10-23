<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\NewsletterMail;
use Modules\Website\Models\Newsletter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NewsletterComposeAutosaveTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

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
    }

    private function draft(array $attributes = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'subject' => 'Original subject',
            'preview_text' => 'Original preview',
            'content' => '<p>Original body</p>',
            'status' => Newsletter::STATUS_DRAFT,
        ], $attributes));
    }

    public function test_autosave_persists_the_editor_content_without_changing_status()
    {
        $newsletter = $this->draft();

        $response = $this->actingAs($this->admin)->postJson(
            route('website.admin.newsletter.campaigns.autosave', $newsletter),
            [
                'subject' => 'Edited subject',
                'preview_text' => 'Edited preview',
                'content' => '<p>Edited body</p>',
            ]
        );

        $response->assertOk();
        $response->assertJsonStructure(['saved_at']);

        $newsletter->refresh();
        $this->assertSame('Edited subject', $newsletter->subject);
        $this->assertSame('Edited preview', $newsletter->preview_text);
        $this->assertSame('<p>Edited body</p>', $newsletter->content);
        $this->assertSame(Newsletter::STATUS_DRAFT, $newsletter->status);
    }

    public function test_autosave_is_refused_once_the_campaign_has_been_sent()
    {
        $newsletter = $this->draft(['status' => Newsletter::STATUS_SENT]);

        $response = $this->actingAs($this->admin)->postJson(
            route('website.admin.newsletter.campaigns.autosave', $newsletter),
            ['content' => '<p>Too late</p>']
        );

        $response->assertStatus(409);
        $this->assertSame('<p>Original body</p>', $newsletter->fresh()->content);
    }

    public function test_test_email_sends_the_unsaved_editor_content()
    {
        Mail::fake();

        $newsletter = $this->draft();

        $response = $this->actingAs($this->admin)->postJson(
            route('website.admin.newsletter.campaigns.test', $newsletter),
            [
                'email' => 'tester@gmail.com',
                'subject' => 'Unsaved subject',
                'preview_text' => 'Unsaved preview',
                'content' => '<p>Unsaved body copy</p>',
            ]
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) {
            return $mail->hasTo('tester@gmail.com')
                && $mail->envelope()->subject === 'Unsaved subject'
                && str_contains($mail->render(), 'Unsaved body copy');
        });

        // The draft itself must not be saved by sending a test.
        $this->assertSame('<p>Original body</p>', $newsletter->fresh()->content);
    }
}
