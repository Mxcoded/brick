<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Exports\GuestsImport;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestTitle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The guest databank and the registration snapshot holding different spellings
 * of the same attribute.
 *
 * Title, identification type and nationality were all free text written by
 * disagreeing flows, and all three were offered as a `<select>` somewhere. That
 * combination is what made the damage silent: a value stored by one surface
 * matched no option on another, rendered blank, and on the guest profile form
 * saving that blank wrote an empty string over a good value.
 */
class GuestAttributeParityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'guests.create', 'guard_name' => 'web']);
    }

    public function test_the_import_reduces_any_title_to_canonical(): void
    {
        foreach (['Mr' => GuestTitle::MR, 'Mrs' => GuestTitle::MRS, 'DR' => GuestTitle::DR] as $offset => $spelling) {
            $phone = '0805555008'.$offset;

            $import = new GuestsImport;
            $import->model([
                'full_name' => 'Title '.$offset,
                'contact_number' => $phone,
                'title' => $spelling,
            ]);

            $this->assertSame(
                GuestTitle::normalise($spelling),
                Guest::where('contact_number', $phone)->value('title'),
                "'{$spelling}' should import as one canonical title.",
            );
        }
    }

    public function test_the_import_reduces_any_identification_type_to_canonical(): void
    {
        $cases = [
            1 => ['NIN', GuestIdentificationType::NIN],
            2 => ['National ID (NIN)', GuestIdentificationType::NIN],
            3 => ['Drivers License', GuestIdentificationType::DRIVERS_LICENCE],
            4 => ["Driver's License", GuestIdentificationType::DRIVERS_LICENCE],
            5 => ['BVN', GuestIdentificationType::BVN],
        ];

        foreach ($cases as $offset => [$spelling, $expected]) {
            $phone = '0805555009'.$offset;

            $import = new GuestsImport;
            $import->model([
                'full_name' => 'Id Type '.$offset,
                'contact_number' => $phone,
                'identification_type' => $spelling,
            ]);

            $this->assertSame(
                $expected,
                Guest::where('contact_number', $phone)->value('identification_type'),
                "'{$spelling}' should import as one canonical document.",
            );
        }
    }

    public function test_the_import_keeps_a_document_it_has_no_name_for(): void
    {
        // Filing this under "Other" would misdescribe what the guest holds, so
        // the raw answer is stored for a human to review.
        $import = new GuestsImport;
        $import->model([
            'full_name' => 'Residence Permit',
            'contact_number' => '08055550097',
            'identification_type' => 'Residence Permit',
        ]);

        $this->assertSame(
            'Residence Permit',
            Guest::where('contact_number', '08055550097')->value('identification_type'),
        );
    }

    public function test_the_import_reduces_a_demonym_to_the_country_name(): void
    {
        $import = new GuestsImport;
        $import->model([
            'full_name' => 'Demonym Guest',
            'contact_number' => '08055550098',
            'nationality' => 'Nigerian',
        ]);

        $this->assertSame('Nigeria', Guest::where('contact_number', '08055550098')->value('nationality'));
    }

    public function test_a_guest_recorded_with_an_old_spelling_still_resolves_to_one_value(): void
    {
        // This is the profile-form scenario: the row predates the vocabulary,
        // and the forms now have to render it as a selected option rather than
        // blank and then overwrite it on save.
        $guest = Guest::create([
            'full_name' => 'Historic Spelling',
            'contact_number' => '08055550099',
            'title' => 'Mrs',
            'identification_type' => 'NIN',
            'nationality' => 'Nigerian',
        ]);

        foreach (GuestTitle::values() as $option) {
            if (GuestTitle::normalise($guest->title) === $option) {
                $this->assertSame('Mrs.', $option, 'A stored title resolves to exactly one option.');

                return;
            }
        }

        $this->fail('A stored title of "Mrs" matched no option in the select.');
    }

    public function test_the_honorific_keeps_a_married_woman_a_mrs(): void
    {
        // The registration flow used to derive the title from gender alone, which
        // silently downgraded every married woman to "Ms.".
        $guest = Guest::create([
            'full_name' => 'Ada Married',
            'contact_number' => '08055550100',
            'gender' => 'female',
            'title' => 'Mrs',
        ]);

        $this->assertSame(GuestTitle::MRS, GuestTitle::forGuest($guest->title, $guest->gender));
    }

    public function test_a_guest_with_no_title_on_file_still_gets_one_derived(): void
    {
        $guest = Guest::create([
            'full_name' => 'No Title Guest',
            'contact_number' => '08055550101',
            'gender' => 'male',
        ]);

        $this->assertSame(GuestTitle::MR, GuestTitle::forGuest($guest->title, $guest->gender));
    }
}
