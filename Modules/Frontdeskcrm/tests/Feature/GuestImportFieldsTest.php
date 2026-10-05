<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Modules\Frontdeskcrm\Exports\GuestImportInstructionsSheet;
use Modules\Frontdeskcrm\Exports\GuestImportTemplateSheet;
use Modules\Frontdeskcrm\Support\GuestImportFields;
use Tests\TestCase;

/**
 * The file format, defined once and read everywhere.
 *
 * These assertions exist because the guidance was wrong twice. The
 * instructions sheet marked `phone` optional while `guests.contact_number` is
 * NOT NULL, so a file built by following the guide lost every row that had no
 * phone number; and it described duplicate detection as matching on
 * `full_name`, which is not unique and not what the importer does. Nothing
 * caught either, because nothing compared the guide to the importer.
 */
class GuestImportFieldsTest extends TestCase
{
    public function test_only_full_name_and_contact_number_are_required(): void
    {
        $this->assertSame(
            ['full_name', 'contact_number'],
            GuestImportFields::requiredHeadings()
        );
    }

    public function test_the_required_columns_match_the_database(): void
    {
        // contact_number is NOT NULL and unique in the schema, so a row without
        // one cannot be stored however the file is written.
        $columns = collect(Schema::getColumns('guests'))->keyBy('name');

        $this->assertFalse($columns['contact_number']['nullable']);
        $this->assertFalse($columns['full_name']['nullable']);
    }

    public function test_every_field_maps_to_a_real_guests_column(): void
    {
        // A heading the guest table has no column for would silently drop data.
        foreach (GuestImportFields::headings() as $heading) {
            $this->assertTrue(
                Schema::hasColumn('guests', $heading),
                "The import advertises a '{$heading}' column that guests does not have."
            );
        }
    }

    public function test_headings_ignore_case_spaces_and_underscores(): void
    {
        // Someone typing headings by hand should not lose a whole file.
        $this->assertSame('full_name', GuestImportFields::resolveHeader('Full Name'));
        $this->assertSame('full_name', GuestImportFields::resolveHeader('FULL_NAME'));
        $this->assertSame('contact_number', GuestImportFields::resolveHeader('Phone Number'));
        $this->assertSame('contact_number', GuestImportFields::resolveHeader('contact number'));
        $this->assertSame('home_address', GuestImportFields::resolveHeader('Address'));
    }

    public function test_the_legacy_phone_heading_still_imports(): void
    {
        // The shipped template used to call this `phone`. Files already built
        // from it have to keep working.
        $this->assertSame('contact_number', GuestImportFields::resolveHeader('phone'));
    }

    public function test_an_unknown_heading_resolves_to_nothing(): void
    {
        // A misspelling must NOT resolve to a near-miss: "nmae" has to be
        // reported as unrecognised rather than landing in an empty column.
        $this->assertNull(GuestImportFields::resolveHeader('nmae'));
        $this->assertNull(GuestImportFields::resolveHeader('weird_column'));
        $this->assertNull(GuestImportFields::resolveHeader(''));
        $this->assertNull(GuestImportFields::resolveHeader('phonee'));
    }

    public function test_punctuation_variants_of_a_heading_are_accepted(): void
    {
        // Stripping underscores means the unspaced spelling resolves too,
        // which is the lenient behaviour intended for hand-typed headings.
        $this->assertSame('full_name', GuestImportFields::resolveHeader('fullname'));
        $this->assertSame('full_name', GuestImportFields::resolveHeader('Full-Name'));
    }

    public function test_missing_required_columns_are_reported(): void
    {
        $this->assertSame(
            ['contact_number'],
            GuestImportFields::missingRequired(['full_name', 'email', 'gender'])
        );

        $this->assertSame(
            [],
            GuestImportFields::missingRequired(['full_name', 'contact_number'])
        );

        // `phone` counts as satisfying the requirement, so an old file is not
        // flagged for a column it already has under the older name.
        $this->assertSame(
            [],
            GuestImportFields::missingRequired(['Full Name', 'Phone'])
        );
    }

    public function test_unrecognised_headings_are_reported(): void
    {
        $this->assertSame(
            ['nmae', 'weird_column'],
            GuestImportFields::unrecognised(['full_name', 'contact_number', 'nmae', 'weird_column', ''])
        );
    }

    public function test_the_template_ships_exactly_the_documented_headings(): void
    {
        $this->assertSame(
            GuestImportFields::headings(),
            (new GuestImportTemplateSheet)->headings()
        );
    }

    public function test_the_template_example_row_covers_every_heading(): void
    {
        // A template with a blank cell where a column should be teaches the
        // user that the column is optional, when it may not be.
        $row = (new GuestImportTemplateSheet)->collection()->first();

        foreach (GuestImportFields::headings() as $heading) {
            $this->assertArrayHasKey($heading, $row);
            $this->assertNotSame('', trim((string) $row[$heading]), "The template leaves '{$heading}' blank.");
        }
    }

    public function test_the_instructions_sheet_marks_the_phone_number_as_required(): void
    {
        $rows = collect((new GuestImportInstructionsSheet)->array())
            ->filter(fn ($row): bool => isset($row[0]) && $row[0] !== '')
            ->keyBy(fn (array $row): string => (string) $row[0]);

        $this->assertSame('Yes', $rows['contact_number'][1]);
        $this->assertSame('Yes', $rows['full_name'][1]);
        $this->assertSame('No', $rows['email'][1]);
        $this->assertSame('No', $rows['nationality'][1]);
    }

    public function test_the_instructions_sheet_does_not_claim_duplicates_match_on_name(): void
    {
        // The importer matches on contact_number and email. full_name is not
        // unique, and describing it as the match key told users that two people
        // sharing a name were the same person.
        $rows = collect((new GuestImportInstructionsSheet)->array())
            ->filter(fn ($row): bool => is_array($row) && count($row) >= 4)
            ->map(fn (array $row): string => (string) ($row[3] ?? ''))
            ->implode(' ');

        $this->assertStringNotContainsString('matched on full_name', $rows);
    }

    public function test_every_documented_alias_is_accepted_by_the_resolver(): void
    {
        foreach (GuestImportFields::all() as $name => $field) {
            foreach ($field['aliases'] as $alias) {
                $this->assertSame(
                    $name,
                    GuestImportFields::resolveHeader($alias),
                    "The guide offers '{$alias}' as an alias for '{$name}', but it does not resolve."
                );
            }
        }
    }

    public function test_no_alias_collides_with_another_columns_name(): void
    {
        // A collision would make one heading write to two different places.
        $names = GuestImportFields::headings();

        foreach (GuestImportFields::all() as $name => $field) {
            foreach ($field['aliases'] as $alias) {
                $this->assertNotContains(
                    $alias,
                    $names,
                    "'{$alias}' is an alias for '{$name}' but is also a documented column of its own."
                );
            }
        }
    }
}
