<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Modules\Website\Models\NewsletterSubscriber;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NewsletterSubscriberImportTest extends TestCase
{
    use DatabaseTransactions;

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

        $admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);

        $this->actingAs($admin);
    }

    private function csvFile(string $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'subs').'.csv';
        file_put_contents($path, $rows);

        return new UploadedFile($path, 'subscribers.csv', null, null, true);
    }

    private function xlsxFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'subs').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile($path, 'subscribers.xlsx', null, null, true);
    }

    public function test_csv_upload_with_new_subscribers_is_imported()
    {
        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->csvFile(
                "name,email\r\n"
                ."Jane Doe,jane.doe@gmail.com\r\n"
                ."John Smith,john.smith@gmail.com\r\n"
            ),
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('2 new subscribers added', session('success'));
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jane.doe@gmail.com',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'john.smith@gmail.com',
            'is_active' => true,
        ]);
        $this->assertNotNull(NewsletterSubscriber::where('email', 'jane.doe@gmail.com')->first()->unsubscribe_token);
    }

    public function test_existing_active_is_skipped_and_inactive_is_reactivated()
    {
        NewsletterSubscriber::create([
            'name' => 'Active',
            'email' => 'already.active@gmail.com',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        NewsletterSubscriber::create([
            'name' => 'Gone',
            'email' => 'was.unsubscribed@gmail.com',
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->csvFile(
                "name,email\r\n"
                ."Updated Name,already.active@gmail.com\r\n"
                ."Back Again,was.unsubscribed@gmail.com\r\n"
                ."Brand New,brand.new@gmail.com\r\n"
            ),
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 new subscribers added', session('success'));
        $this->assertStringContainsString('1 reactivated', session('success'));
        $this->assertStringContainsString('1 already existed', session('success'));

        $reactivated = NewsletterSubscriber::where('email', 'was.unsubscribed@gmail.com')->first();
        $this->assertTrue($reactivated->is_active);
        $this->assertNull($reactivated->unsubscribed_at);

        $this->assertSame(
            'Updated Name',
            NewsletterSubscriber::where('email', 'already.active@gmail.com')->first()->name
        );
        $this->assertSame(3, NewsletterSubscriber::where('is_active', true)->count());
    }

    public function test_invalid_rows_are_reported_as_failures_without_blocking_valid_rows()
    {
        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->csvFile(
                "name,email\r\n"
                ."Bad Row,not-an-email\r\n"
                ."Good Row,valid.sender@gmail.com\r\n"
            ),
        ]);

        $response->assertSessionHas('import_failures', 1);
        $details = session('import_failure_details');
        $this->assertCount(1, $details);
        $this->assertSame('not-an-email', $details[0]['email']);
        $this->assertStringContainsString('not a valid email address', $details[0]['error']);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'valid.sender@gmail.com']);
        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'not-an-email']);
    }

    public function test_xlsx_upload_is_imported()
    {
        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->xlsxFile([
                ['name', 'email'],
                ['Excel User', 'excel.user@gmail.com'],
            ]),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'excel.user@gmail.com',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_email_within_the_same_file_is_only_counted_once()
    {
        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->csvFile(
                "name,email\r\n"
                ."First,duplicate@gmail.com\r\n"
                ."Second,duplicate@gmail.com\r\n"
            ),
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(1, NewsletterSubscriber::where('email', 'duplicate@gmail.com')->count());
    }

    public function test_missing_email_column_fails_the_row_instead_of_erroring()
    {
        $response = $this->post(route('website.admin.newsletter.subscribers.import'), [
            'file' => $this->csvFile("name\r\nNo Email Here\r\n"),
        ]);

        $response->assertSessionHas('import_failures', 1);
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_unreadable_xlsx_returns_a_friendly_error_instead_of_a_500()
    {
        $file = UploadedFile::fake()->createWithContent('subscribers.xlsx', 'this is not a spreadsheet');

        $response = $this->post(route('website.admin.newsletter.subscribers.import'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_official_sample_template_rows_pass_the_import()
    {
        $sample = $this->get(route('website.admin.newsletter.subscribers.import.sample'));
        $sample->assertOk();

        $file = UploadedFile::fake()->createWithContent('newsletter_import_sample.csv', $sample->streamedContent());

        $response = $this->post(route('website.admin.newsletter.subscribers.import'), ['file' => $file]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('2 new subscribers added', session('success'));
        $this->assertSame(0, session('import_failures'));
    }
}
