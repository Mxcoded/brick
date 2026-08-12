<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Modules\Frontdeskcrm\Models\Guest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GuestImportTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'guests.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['guests.create', 'access_frontdesk_dashboard']);
    }

    public function test_import_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('frontdesk.guests.import'));

        $response->assertStatus(200);
        $response->assertSee('Import Guests');
        $response->assertSee('Download Excel Guide');
    }

    public function test_import_template_is_downloadable(): void
    {
        $response = $this->actingAs($this->user)->get(route('frontdesk.guests.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('guest-import-guide.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_import_template_requires_permission(): void
    {
        $plainUser = User::factory()->create();

        $this->actingAs($plainUser)->get(route('frontdesk.guests.import.template'))
            ->assertForbidden();
    }

    public function test_guest_csv_is_imported(): void
    {
        $csv = implode("\n", [
            'full_name,title,email,phone,nationality,gender,birthday,occupation,company,address,city,state,zip_code,identification_type,identification_number,emergency_name,emergency_relationship,emergency_phone',
            'Alice Guest,Ms,alice@example.com,08011112222,Nigerian,Female,1995-05-05,Doctor,Health Ltd,12 Test Rd,Abuja,FCT,900001,NIN,11111111111,Bob Guest,Brother,08033334444',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $response = $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('frontdesk.guests.index'));
        $response->assertSessionHas('success');

        $guest = Guest::where('full_name', 'Alice Guest')->first();
        $this->assertNotNull($guest);
        $this->assertSame('alice@example.com', $guest->email);
        $this->assertSame('08011112222', $guest->contact_number);
        $this->assertSame('Ms', $guest->title);
        $this->assertSame('Nigerian', $guest->nationality);
    }

    public function test_duplicate_guest_rows_are_skipped(): void
    {
        $csv = implode("\n", [
            'full_name,email,phone',
            'Dup Guest,dup@example.com,08055556666',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), ['file' => $file]);
        $response = $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), ['file' => $file]);

        $response->assertSessionHas('success', fn ($message) => str_contains($message, 'skipped (duplicates)'));
        $this->assertSame(1, Guest::where('full_name', 'Dup Guest')->count());
    }
}
