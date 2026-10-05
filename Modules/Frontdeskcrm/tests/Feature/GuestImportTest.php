<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Frontdeskcrm\Exports\GuestsImport;
use Modules\Frontdeskcrm\Filament\Pages\ImportGuests;
use Modules\Frontdeskcrm\Models\Guest;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        Permission::firstOrCreate(['name' => 'guests.import', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);

        // staff/active so the user can reach the panel at all: canAccessPanel
        // rejects anyone without a reachable resource before the page's own
        // permission check ever runs.
        $this->user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);

        $this->user->givePermissionTo(['guests.create', 'guests.import', 'access_frontdesk_dashboard']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_legacy_import_route_redirects_to_the_filament_page(): void
    {
        // The Blade form is gone. The route name survives so the old
        // bookmarks and the Blade guest views' links keep working.
        $response = $this->actingAs($this->user)->get(route('frontdesk.guests.import'));

        $response->assertRedirect(ImportGuests::getUrl());
    }

    public function test_import_template_is_downloadable(): void
    {
        $response = $this->actingAs($this->user)->get(route('frontdesk.guests.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('guest-import-guide.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_the_guide_downloads_from_the_filament_page(): void
    {
        // Called directly rather than through the Livewire action: this is the
        // one path that used to throw, because Excel::raw() wants the writer
        // type before its callback, not a closure in its place.
        Livewire::actingAs($this->user);

        $response = app(ImportGuests::class)->downloadGuide();

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString(
            'guest-import-guide.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_the_guide_action_is_wired_on_the_page(): void
    {
        Livewire::actingAs($this->user)
            ->test(ImportGuests::class)
            ->assertActionExists('downloadGuide')
            ->assertActionExists('import');
    }

    public function test_a_csv_uploaded_on_the_page_is_imported(): void
    {
        Storage::fake('local');

        $csv = implode("\n", [
            'full_name,email,phone,gender',
            'Ada Filament,ada.filament@example.com,08055550020,female',
            'Grace Filament,grace.filament@example.com,08055550021,',
        ]);

        Livewire::actingAs($this->user)
            ->test(ImportGuests::class)
            ->fillForm([
                'file' => [UploadedFile::fake()->createWithContent('guests.csv', $csv)],
            ])
            ->call('runImport')
            ->assertHasNoFormErrors();

        // gender is normalised to the vocabulary the directory filter reads.
        $this->assertSame('Female', Guest::where('email', 'ada.filament@example.com')->value('gender'));
        $this->assertSame(2, Guest::whereIn('email', [
            'ada.filament@example.com',
            'grace.filament@example.com',
        ])->count());
    }

    public function test_the_page_reports_the_rows_it_refused(): void
    {
        Storage::fake('local');

        // One good row, one blank name, one with no phone number.
        $csv = implode("\n", [
            'full_name,email,phone',
            'Kept Filament,kept@example.com,08055550022',
            ',blank@example.com,08055550023',
            'No Number,nonumber@example.com,',
        ]);

        Livewire::actingAs($this->user)
            ->test(ImportGuests::class)
            ->fillForm([
                'file' => [UploadedFile::fake()->createWithContent('guests.csv', $csv)],
            ])
            ->call('runImport')
            // The report is what makes the failures findable, so assert it
            // appears on screen rather than only in the import object.
            ->assertSee('Import result')
            ->assertSee('Missing guest name')
            ->assertSee('Missing phone number');

        $this->assertSame(1, Guest::where('full_name', 'Kept Filament')->count());
    }

    public function test_the_uploaded_file_is_deleted_after_the_run(): void
    {
        Storage::fake('local');

        $csv = implode("\n", [
            'full_name,email,phone',
            'Ada Cleaned,cleaned@example.com,08055550024',
        ]);

        Livewire::actingAs($this->user)
            ->test(ImportGuests::class)
            ->fillForm([
                'file' => [UploadedFile::fake()->createWithContent('guests.csv', $csv)],
            ])
            ->call('runImport');

        // Guest files hold names, phone numbers and ID numbers, so they should
        // not be left on the server once the run has happened.
        $this->assertSame([], Storage::disk('local')->files('guest-imports'));
    }

    public function test_import_template_requires_permission(): void
    {
        $plainUser = User::factory()->create();

        $this->actingAs($plainUser)->get(route('frontdesk.guests.import.template'))
            ->assertForbidden();
    }

    public function test_the_page_shows_which_columns_are_required_and_optional(): void
    {
        $this->actingAs($this->user)
            ->get(ImportGuests::getUrl())
            ->assertSuccessful()
            // The whole point of the screen: a user should be able to see what
            // is required without downloading anything.
            ->assertSee('Which columns to include')
            ->assertSee('full_name')
            ->assertSee('contact_number')
            ->assertSee('Required')
            ->assertSee('Optional');
    }

    public function test_a_file_missing_a_required_column_is_flagged_before_importing(): void
    {
        $html = $this->withUploadedHeaderRow('full_name,email');

        $this->assertStringContainsString('Missing required column', $html);
        $this->assertStringContainsString('contact_number', $html);
    }

    public function test_a_misspelled_column_is_reported_as_unrecognised(): void
    {
        // "nmae" is a typo for full_name. Without this the row would import with
        // no name at all and be skipped with no clue why.
        $html = $this->withUploadedHeaderRow('nmae,phone');

        $this->assertStringContainsString('Not recognised', $html);
        $this->assertStringContainsString('nmae', $html);
    }

    public function test_a_correct_file_is_confirmed_rather_than_silently_accepted(): void
    {
        $this->assertStringContainsString(
            'File looks good',
            $this->withUploadedHeaderRow('full_name,contact_number,email')
        );
    }

    public function test_the_legacy_phone_heading_satisfies_the_required_column(): void
    {
        // A file built from the previous guide says `phone`, not
        // contact_number. It must not be warned about a column it already has.
        $html = $this->withUploadedHeaderRow('full_name,phone');

        $this->assertStringContainsString('File looks good', $html);
        $this->assertStringNotContainsString('Missing required column', $html);
    }

    /**
     * Put a file with this header row where the page will find it, and return
     * the rendered page.
     *
     * Filament only moves the upload onto its disk during validation, so
     * fillForm() alone leaves nothing readable. Writing the file straight to the
     * disk is what the page actually sees once a real upload has completed.
     */
    protected function withUploadedHeaderRow(string $headerRow): string
    {
        Storage::fake('local');

        $path = 'guest-imports/check.csv';
        Storage::disk('local')->put($path, $headerRow."\nRow One,08055550025\n");

        return (string) Livewire::actingAs($this->user)
            ->test(ImportGuests::class)
            ->set('data.file', $path)
            ->html();
    }

    public function test_import_page_requires_the_import_permission(): void
    {
        // guests.create alone is deliberately not enough: the import permission
        // is separate because one import writes thousands of rows where a
        // single create writes one.
        //
        // guests.read is granted as well, otherwise the user never reaches the
        // page at all - User::canAccessPanel rejects them at the panel door and
        // redirects, which would pass whether or not the page checked anything.
        $createOnly = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $createOnly->givePermissionTo(['guests.read', 'guests.create', 'access_frontdesk_dashboard']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($createOnly)->get(ImportGuests::getUrl())
            ->assertForbidden();
    }

    public function test_a_user_holding_the_import_permission_may_open_the_page(): void
    {
        // Counterpart to the test above, so the 403 there is the page refusing
        // rather than the panel refusing.
        $importer = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $importer->givePermissionTo(['guests.read', 'guests.import', 'access_frontdesk_dashboard']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($importer)->get(ImportGuests::getUrl())
            ->assertSuccessful();
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

        $response->assertSessionHas('success');
        $response->assertSessionHas('success', fn ($message) => str_contains($message, '1 guests imported'));

        $guest = Guest::where('full_name', 'Alice Guest')->first();
        $this->assertNotNull($guest);
        $this->assertSame('alice@example.com', $guest->email);
        $this->assertSame('08011112222', $guest->contact_number);
        // The sheet spells these the way the import guide documents them, which is
        // not the canonical form the forms offer. All three are folded on the way
        // in so the same person cannot be recorded three ways depending on which
        // screen or spreadsheet they came from.
        $this->assertSame('Ms.', $guest->title);
        $this->assertSame('Nigeria', $guest->nationality);
        $this->assertSame('National ID (NIN)', $guest->identification_type);
        // gender is normalised to the vocabulary the directory filter reads.
        $this->assertSame('Female', $guest->gender);
        $this->assertSame('08033334444', $guest->emergency_contact);
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

        $response->assertSessionHas('success');
        $this->assertSame(1, Guest::where('full_name', 'Dup Guest')->count());
    }

    public function test_a_second_guest_with_the_same_name_is_not_treated_as_a_duplicate(): void
    {
        // full_name is not unique and is not a safe identifier. Matching on it
        // - which the Excel path used to do - silently dropped the second of
        // any two people who share a name.
        $csv = implode("\n", [
            'full_name,email,phone',
            'Chinedu Balogun,first@example.com,08055550001',
            'Chinedu Balogun,second@example.com,08055550002',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), ['file' => $file]);

        $this->assertSame(2, Guest::where('full_name', 'Chinedu Balogun')->count());
    }

    public function test_a_row_missing_a_phone_number_is_reported_rather_than_crashing_the_file(): void
    {
        $csv = implode("\n", [
            'full_name,email,phone',
            'Good Guest,good@example.com,08055550009',
            'No Phone,phone@example.com,',
            'Another Good,another@example.com,08055550010',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), ['file' => $file]);

        // The bad row costs one row, not the whole file.
        $this->assertSame(2, Guest::whereIn('email', ['good@example.com', 'another@example.com'])->count());
        $this->assertNull(Guest::where('email', 'phone@example.com')->first());
    }

    public function test_rejected_rows_carry_a_row_number_and_a_reason(): void
    {
        // The old screen reported only "N skipped (duplicates)", so a rejected
        // row could not be found or diagnosed. Every rejection now names the row.
        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Kept Guest',
            'email' => 'kept@example.com',
            'contact_number' => '08055550011',
        ]);

        $import->model([
            'full_name' => '',
            'email' => 'nameless@example.com',
            'contact_number' => '08055550012',
        ]);

        $import->model([
            'full_name' => 'No Number',
            'email' => 'nonumber@example.com',
            'contact_number' => '',
        ]);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(2, $import->getSkippedCount());

        $rejections = $import->getRejections();

        // Row 1 is the heading row, so the three calls above are file rows 2, 3
        // and 4 - the kept guest first, then the two refusals. These used to
        // assert 2 and 3, which pointed one row above the offending line, so
        // fixing the wrong row fixed nothing.
        $this->assertSame(3, $rejections[0]['row']);
        $this->assertSame('Missing guest name', $rejections[0]['reason']);
        $this->assertSame(4, $rejections[1]['row']);
        $this->assertSame('Missing phone number', $rejections[1]['reason']);
    }

    public function test_a_rejection_names_the_existing_guest_it_matched(): void
    {
        Guest::create([
            'full_name' => 'Existing Guest',
            'contact_number' => '08055550013',
            'email' => 'existing@example.com',
        ]);

        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Totally Different Name',
            'email' => 'existing@example.com',
            'contact_number' => '08055559999',
        ]);

        $rejections = $import->getRejections();

        $this->assertCount(1, $rejections);
        $this->assertSame('Already on file', $rejections[0]['reason']);
        $this->assertStringContainsString('Existing Guest', (string) $rejections[0]['detail']);
        $this->assertStringContainsString('email', (string) $rejections[0]['detail']);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function reformattedPhoneProvider(): array
    {
        return [
            'spaces' => ['0801 111 2222', '08011112222'],
            'dashes' => ['0801-111-2222', '08011112222'],
            'parentheses' => ['(0801) 111 2222', '08011112222'],
            'trailing whitespace' => ['08011112222 ', '08011112222'],
            // The column holds both spellings, so both directions must match.
            'local typed against an international guest' => ['08011112222', '+2348011112222'],
            'international typed against a local guest' => ['+234 801 111 2222', '08011112222'],
            'international without the plus' => ['2348011112222', '+2348011112222'],
        ];
    }

    /**
     * A reformatted number must be recognised as the same guest, not shoved at
     * the database until the UNIQUE index rejects it.
     *
     * The old behaviour was worse than a crash: the lookup compared raw strings,
     * so `0801 111 2222` missed the guest stored as `08011112222`, the insert
     * then failed on the unique index, and the row was reported as "Could not
     * save" - a database error dressed up as a verdict, with nothing suggesting
     * the guest already exists.
     */
    #[DataProvider('reformattedPhoneProvider')]
    public function test_a_reformatted_number_is_reported_as_the_same_guest(string $incoming, string $stored): void
    {
        Guest::create([
            'full_name' => 'Existing Guest',
            'contact_number' => $stored,
        ]);

        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Same Person, Different Spelling',
            'contact_number' => $incoming,
        ]);

        $rejections = $import->getRejections();

        $this->assertSame(0, $import->getImportedCount());
        $this->assertCount(1, $rejections);
        $this->assertSame('Already on file', $rejections[0]['reason']);
        $this->assertStringContainsString('contact_number', (string) $rejections[0]['detail']);

        // And the report must not leak a raw database error.
        $this->assertStringNotContainsString('Could not save', $rejections[0]['reason']);

        $this->assertSame(1, Guest::where('contact_number', $stored)->count());
    }

    public function test_a_genuinely_different_number_is_not_treated_as_a_duplicate(): void
    {
        // The cost of getting the above wrong in the other direction: a real
        // guest refused their own profile over one digit.
        Guest::create([
            'full_name' => 'Existing Guest',
            'contact_number' => '08011112222',
        ]);

        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Different Person',
            'contact_number' => '0801 111 2223',
        ]);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame([], $import->getRejections());
        $this->assertSame(2, Guest::count());
    }

    public function test_a_shared_prefix_is_not_a_duplicate(): void
    {
        // Short-prefix near misses are the ones a sloppy comparison would merge.
        Guest::create([
            'full_name' => 'Existing Guest',
            'contact_number' => '0801111222',
        ]);

        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Different Person',
            'contact_number' => '08011112222',
        ]);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame([], $import->getRejections());
    }

    public function test_a_blank_phone_does_not_match_every_other_blank(): void
    {
        // "" has the same digits as every other "", so a naive comparison would
        // make one empty contact_number collide with all of them.
        Guest::create([
            'full_name' => 'Existing Guest',
            'contact_number' => '08011112222',
        ]);

        $import = new GuestsImport;

        // A blank phone is refused on its own merits before any lookup happens.
        $import->model([
            'full_name' => 'No Number',
            'contact_number' => '   ',
        ]);

        $rejections = $import->getRejections();

        $this->assertCount(1, $rejections);
        $this->assertSame('Missing phone number', $rejections[0]['reason']);
        $this->assertSame(0, $import->getImportedCount());
    }
}
