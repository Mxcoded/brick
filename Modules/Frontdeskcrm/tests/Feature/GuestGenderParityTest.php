<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Exports\GuestsImport;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\GuestGender;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The guest databank and the registration snapshot holding different spellings
 * of the same attribute.
 *
 * `guests.gender` is free text and `registrations.gender` is an enum that
 * MySQL coerces case-insensitively, so nothing rejected a lowercase guest. The
 * damage was in the comparison: the registration flow derived an honorific with
 * `$guest->gender == 'Male'`, which a lowercase value fails.
 */
class GuestGenderParityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'guests.create', 'guard_name' => 'web']);
    }

    public function test_a_lowercase_guest_still_gets_the_right_honorific(): void
    {
        // The regression: this guest booked through the public site, which
        // wrote `male`. Before the fix he was prefilled "Ms." on the
        // registration form.
        $guest = Guest::create([
            'full_name' => 'Lowercase Male',
            'contact_number' => '08055550061',
            'gender' => 'male',
        ]);

        $this->assertSame('Mr.', GuestGender::honorific($guest->gender));
    }

    public function test_the_honorific_agrees_across_every_spelling(): void
    {
        foreach (['male', 'Male', 'MALE'] as $index => $spelling) {
            $guest = Guest::create([
                'full_name' => 'Spelling '.$spelling,
                'contact_number' => '0805555006'.$index,
                'gender' => $spelling,
            ]);

            $this->assertSame(
                'Mr.',
                GuestGender::honorific($guest->gender),
                "'{$spelling}' should derive the same honorific.",
            );
        }
    }

    public function test_the_import_snapshot_matches_the_databank(): void
    {
        // A registration snapshots the guest's attributes. The two spellings
        // must not drift, or a stay recorded against a guest no longer
        // describes that guest.
        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Snapshot Guest',
            'contact_number' => '08055550062',
            'gender' => 'male',
        ]);

        $guest = Guest::where('contact_number', '08055550062')->first();

        $this->assertNotNull($guest);
        $this->assertSame(
            GuestGender::MALE,
            $guest->gender,
            'The databank holds the canonical value.',
        );
        $this->assertSame(
            GuestGender::forRegistration($guest->gender),
            GuestGender::forRegistration('male'),
            'Both sides reduce to the same enum member.',
        );
    }

    public function test_the_import_reduces_any_spelling_to_canonical(): void
    {
        foreach ([1 => 'm', 2 => 'FEMALE', 3 => 'Other'] as $offset => $spelling) {
            $phone = '0805555007'.$offset;

            $import = new GuestsImport;
            $import->model([
                'full_name' => 'Import '.$offset,
                'contact_number' => $phone,
                'gender' => $spelling,
            ]);

            $this->assertSame(
                GuestGender::normalise($spelling),
                Guest::where('contact_number', $phone)->value('gender'),
                "'{$spelling}' should import as one canonical value.",
            );
        }
    }

    public function test_an_unrecognised_gender_does_not_overflow_the_column(): void
    {
        // The importer used to reject anything over 20 characters and claim
        // that was the column width. It was not, and the check was arbitrary.
        $import = new GuestsImport;

        $import->model([
            'full_name' => 'Long Gender',
            'contact_number' => '08055550073',
            'gender' => str_repeat('x', 300),
        ]);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(
            GuestGender::OTHER,
            Guest::where('contact_number', '08055550073')->value('gender'),
        );
    }

    public function test_a_guest_with_no_gender_stays_unstated(): void
    {
        $import = new GuestsImport;

        $import->model([
            'full_name' => 'No Gender',
            'contact_number' => '08055550074',
            'gender' => '',
        ]);

        $this->assertNull(
            Guest::where('contact_number', '08055550074')->value('gender'),
            'An unstated gender must not be recorded as Other.',
        );
        $this->assertNull(GuestGender::honorific(''));
    }

    public function test_the_directory_filter_finds_both_spellings(): void
    {
        // The table filter compares with lower() rather than by exact value,
        // so a guest still stored lowercase is not silently dropped from the
        // Male filter.
        Guest::create([
            'full_name' => 'Canonical Male',
            'contact_number' => '08055550081',
            'gender' => 'Male',
        ]);

        Guest::create([
            'full_name' => 'Legacy Lowercase Male',
            'contact_number' => '08055550082',
            'gender' => 'male',
        ]);

        $found = Guest::whereRaw('lower(gender) = ?', ['male'])->pluck('full_name');

        $this->assertCount(2, $found);
        $this->assertContains('Canonical Male', $found);
        $this->assertContains('Legacy Lowercase Male', $found);
    }
}
