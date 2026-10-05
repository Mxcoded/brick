<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelWriterType;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Frontdeskcrm\Exports\GuestImportGuide;
use Modules\Frontdeskcrm\Exports\GuestsExcelImport;
use Modules\Frontdeskcrm\Filament\Pages\ImportGuests;
use Modules\Frontdeskcrm\Models\Guest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Uploading the downloaded guide back, which is what users actually do.
 *
 * The guide is a two-sheet workbook, so this is also the only path that
 * exercises the multi-sheet reader. It is a different code path from CSV and it
 * used to fail in ways no test covered: a WithValidation rules failure aborts
 * the entire file, so one mistyped email in the filled-in rows threw the whole
 * thing away and the screen said "Could not read that file" - accusing the user
 * of uploading a corrupt file when the file was fine.
 */
class GuestImportRoundTripTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'guests.create', 'guard_name' => 'web']);
        // The import route is gated on guests.import, which is deliberately
        // separate from guests.create - one import writes thousands of rows.
        Permission::firstOrCreate(['name' => 'guests.import', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['guests.create', 'guests.import', 'access_frontdesk_dashboard']);
    }

    public function test_downloaded_guide_can_be_reimported(): void
    {
        $import = $this->importBytes($this->guideBytes());

        $this->assertSame(1, $import->getImportedCount());
        $this->assertNotNull(Guest::where('full_name', 'John Doe')->first());
    }

    public function test_the_guide_reimports_with_guest_rows_filled_in(): void
    {
        // The real case: download, fill in a few guests, upload back.
        $import = $this->importBytes(
            $this->guideBytesWithRows([
                ['Ada Lovelace', '08055550040', 'ada@example.com'],
                ['Grace Hopper', '08055550041', 'grace@example.com'],
            ])
        );

        $this->assertSame(3, $import->getImportedCount());
        $this->assertNotNull(Guest::where('full_name', 'Grace Hopper')->first());
    }

    public function test_one_bad_email_does_not_discard_the_good_rows(): void
    {
        // This is the bug that produced "Could not read that file": the old
        // rules() lived in WithValidation, which throws, and a throw inside an
        // import takes the whole file with it. Two good guests and one typo used
        // to mean zero guests and a message blaming the file format.
        //
        // The example row is cleared first so the counts are about the rows
        // added here, and so the reported row number lines up with the file.
        $import = $this->importBytes(
            $this->guideBytesWithRows(
                [
                    ['Ada Lovelace', '08055550042', 'ada@example.com'],
                    ['Typo Email', '08055550043', 'not-an-email'],
                    ['Grace Hopper', '08055550044', 'grace@example.com'],
                ],
                clearExampleRow: true
            )
        );

        $this->assertCount(1, $import->getRejections());
        $this->assertSame(2, $import->getImportedCount());
        $this->assertNotNull(Guest::where('full_name', 'Ada Lovelace')->first());
        $this->assertNotNull(Guest::where('full_name', 'Grace Hopper')->first());

        // And the typo is reported by name, against the row it is really on:
        // row 1 is the heading row and row 2 is the first guest.
        $rejections = $import->getRejections();

        $this->assertCount(1, $rejections);
        $this->assertSame(3, $rejections[0]['row']);
        $this->assertStringContainsString('Email', $rejections[0]['reason']);
        $this->assertStringContainsString('not-an-email', (string) $rejections[0]['detail']);
    }

    public function test_a_too_long_name_is_reported_without_discarding_the_file(): void
    {
        $import = $this->importBytes(
            $this->guideBytesWithRows([
                [str_repeat('N', 300), '08055550045', 'long@example.com'],
                ['Ada Lovelace', '08055550046', 'ada@example.com'],
            ])
        );

        $this->assertSame(2, $import->getImportedCount());

        $rejections = $import->getRejections();

        $this->assertCount(1, $rejections);
        $this->assertStringContainsString('too long', $rejections[0]['reason']);
    }

    public function test_blank_lines_are_not_reported_as_guests(): void
    {
        // Excel hands over rows holding only formatting as arrays of empty
        // strings. Reporting those as "Missing guest name" buried the real
        // problems under noise about nothing.
        $import = $this->importBytes(
            $this->guideBytesWithRows(
                [
                    ['Ada Lovelace', '08055550047', 'ada@example.com'],
                    ['', '', ''],
                    ['Grace Hopper', '08055550048', 'grace@example.com'],
                ],
                clearExampleRow: true
            )
        );

        $this->assertSame(2, $import->getImportedCount());
        $this->assertSame([], $import->getRejections());
    }

    public function test_a_file_with_no_recognised_headings_imports_nothing(): void
    {
        // Every column misspelled. The import screen turns this into "you sent
        // the wrong sheet" using the heading row; here it just has to be true
        // that no row was ever a candidate.
        $import = $this->importBytes(
            $this->guideBytesWithRows(
                [['Ada Lovelace', '08055550049', 'ada@example.com']],
                headings: ['nmae', 'phoen', 'emial'],
                clearExampleRow: true
            )
        );

        $this->assertSame(0, $import->getImportedCount());
        $this->assertFalse($import->sawRecognisedHeading());
        $this->assertSame(0, Guest::count());
    }

    public function test_a_genuinely_empty_file_is_not_an_error(): void
    {
        $import = $this->importBytes(
            $this->guideBytesWithRows([], headings: ['full_name', 'contact_number', 'email'], clearExampleRow: true)
        );

        // Importing a template the user filled in nothing on purpose is a
        // legitimate thing to do. The headings were valid, so this is an import
        // of zero rows rather than the "wrong sheet" case, which is decided on
        // the heading row and cannot be told apart here.
        $this->assertSame(0, $import->getImportedCount());
        $this->assertSame([], $import->getRejections());
    }

    public function test_a_corrupt_file_does_not_import_anything(): void
    {
        $this->expectException(\Throwable::class);

        $import = $this->importBytes("PK\x03\x04not-really-a-zip-file");

        $this->assertSame(0, $import->getImportedCount());
    }

    /**
     * Import bytes and hand back the import object so the report can be read.
     *
     * Driven directly rather than through the page, because the page builds its
     * import internally and discards it; what is under test here is the import
     * itself. The upload route is covered separately below, and the page's own
     * wording in GuestImportTest.
     *
     * The bytes go to a temp file rather than to storage/app/private so a failing
     * import cannot leave a stray workbook behind, which is how the corrupt-file
     * test used to leave one.
     */
    protected function importBytes(string $bytes): GuestsExcelImport
    {
        $path = $this->temporaryFile($bytes, 'xlsx');

        try {
            $import = new GuestsExcelImport;

            Excel::import($import, $path);

            return $import;
        } finally {
            @unlink($path);
        }
    }

    protected function temporaryFile(string $contents, string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'guest-import').'.'.$extension;
        file_put_contents($path, $contents);

        return $path;
    }

    public function test_the_guide_reimports_through_the_legacy_upload_route(): void
    {
        // The one test that goes through the controller, to prove the real
        // upload route still works after the import rules moved.
        $file = UploadedFile::fake()->createWithContent(
            'guest-import-guide.xlsx',
            $this->guideBytesWithRows([['Ada Lovelace', '08055550050', 'ada@example.com']])
        );

        $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), [
            'file' => $file,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(ImportGuests::getUrl());

        $this->assertNotNull(Guest::where('full_name', 'Ada Lovelace')->first());
    }

    /**
     * The downloaded guide, in memory.
     *
     * Written to a temp file rather than stored, so running the suite does not
     * litter storage/app/private with workbooks.
     */
    protected function guideBytes(): string
    {
        return (string) Excel::raw(new GuestImportGuide, ExcelWriterType::XLSX);
    }

    /**
     * The downloaded guide, with extra rows added to its template sheet exactly
     * as a user filling it in would.
     *
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, string>|null  $headings  Replaces the template's own headings.
     * @param  bool  $clearExampleRow  Deletes the template's sample row, which is
     *                                 what a user does before typing their own guests over it.
     */
    protected function guideBytesWithRows(array $rows, ?array $headings = null, bool $clearExampleRow = false): string
    {
        $temporary = $this->temporaryFile($this->guideBytes(), 'xlsx');

        try {
            $spreadsheet = IOFactory::load($temporary);
            $sheet = $spreadsheet->getSheetByName('Guest Template');

            if ($headings !== null) {
                // Overwrite the heading row in place so the column count is
                // unchanged.
                $sheet->fromArray($headings, null, 'A1');
            }

            // The sample row would otherwise be imported as a guest called John
            // Doe, which is exactly what a user clearing it before typing their
            // own avoids.
            if ($clearExampleRow) {
                $sheet->removeRow(2);
            }

            $next = $sheet->getHighestDataRow() + 1;

            foreach ($rows as $row) {
                $sheet->fromArray($row, null, 'A'.$next);
                $next++;
            }

            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($temporary);

            return (string) file_get_contents($temporary);
        } finally {
            @unlink($temporary);
        }
    }
}
