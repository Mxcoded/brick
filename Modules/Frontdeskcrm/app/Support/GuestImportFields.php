<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * The guest-import file format, in one place.
 *
 * The column list used to be written out four times: in the downloadable
 * template, in the instructions sheet, in the import's own column mapping, and
 * in the notes on the import screen. They drifted. The instructions sheet
 * marked `phone` as optional while the database column is NOT NULL, so a file
 * built from the guide lost every row that followed the guidance; and it still
 * said duplicates were matched on `full_name`, which is neither unique nor what
 * the importer does.
 *
 * One definition, read by all four, so the guide and the behaviour cannot
 * disagree.
 */
final class GuestImportFields
{
    /**
     * Every accepted column, in the order the template ships them.
     *
     * name       the header to use; also the guests column it is written to
     * aliases    other headers accepted for the same value
     * required   whether a row without it can be stored at all
     * example    what the template's sample row shows
     * notes      shown in the guide and on the import screen
     *
     * @return array<string, array{aliases: array<int, string>, required: bool, example: string, notes: string}>
     */
    public static function all(): array
    {
        return [
            'full_name' => [
                'aliases' => [],
                'required' => true,
                'example' => 'John Doe',
                'notes' => 'The guest\'s name as it should appear on the guest card and folio.',
            ],

            'contact_number' => [
                // "phone" is what the shipped template used to call this, so
                // files already in circulation keep importing.
                'aliases' => ['phone', 'phone_number'],
                'required' => true,
                'example' => '08012345678',
                'notes' => 'Required. Guests have no profile without a phone number, and this is also one of the two columns used to spot someone who is already on file.',
            ],

            'email' => [
                'aliases' => [],
                'required' => false,
                'example' => 'john.doe@example.com',
                'notes' => 'Optional, but the other column used to spot a guest who is already on file. Must be a valid address if given.',
            ],

            'title' => [
                'aliases' => [],
                'required' => false,
                'example' => GuestTitle::MR,
                'notes' => 'Optional. Mr., Mrs., Ms., Miss, Dr., Prof. or Chief. The period is optional on the way in and is added for you.',
            ],

            'gender' => [
                'aliases' => [],
                'required' => false,
                'example' => GuestGender::MALE,
                'notes' => 'Optional. Male, Female or Other, with or without capitals. Anything else is stored as Other.',
            ],

            'birthday' => [
                'aliases' => ['date_of_birth', 'dob'],
                'required' => false,
                'example' => '1990-01-15',
                'notes' => 'Optional. Use YYYY-MM-DD so it is not read as a US date.',
            ],

            'nationality' => [
                'aliases' => [],
                'required' => false,
                'example' => GuestNationality::normalise('Nigerian'),
                'notes' => 'Optional. A country name is stored as-is, and common demonyms and abbreviations such as Nigerian, UK or USA are converted to the country name.',
            ],

            'identification_type' => [
                'aliases' => ['id_type'],
                'required' => false,
                'example' => GuestIdentificationType::NIN,
                'notes' => 'Optional, but shown on the guest card. Short forms are accepted and stored in full, so NIN, BVN, Passport and Driver Licence all work.',
            ],

            'identification_number' => [
                'aliases' => ['id_number'],
                'required' => false,
                'example' => '12345678901',
                'notes' => 'Optional.',
            ],

            'occupation' => [
                'aliases' => [],
                'required' => false,
                'example' => 'Software Engineer',
                'notes' => 'Optional.',
            ],

            'company_name' => [
                'aliases' => ['company'],
                'required' => false,
                'example' => 'Acme Ltd',
                'notes' => 'Optional.',
            ],

            'home_address' => [
                'aliases' => ['address'],
                'required' => false,
                'example' => '24 Jose Marti Crescent, Asokoro',
                'notes' => 'Optional. One line is enough.',
            ],

            'city' => [
                'aliases' => [],
                'required' => false,
                'example' => 'Abuja',
                'notes' => 'Optional.',
            ],

            'state' => [
                'aliases' => [],
                'required' => false,
                'example' => 'FCT',
                'notes' => 'Optional.',
            ],

            'zip_code' => [
                'aliases' => ['zip'],
                'required' => false,
                'example' => '900001',
                'notes' => 'Optional.',
            ],

            'emergency_name' => [
                'aliases' => ['emergency_contact_name'],
                'required' => false,
                'example' => 'Jane Doe',
                'notes' => 'Optional.',
            ],

            'emergency_relationship' => [
                'aliases' => [],
                'required' => false,
                'example' => 'Spouse',
                'notes' => 'Optional.',
            ],

            'emergency_contact' => [
                'aliases' => ['emergency_phone'],
                'required' => false,
                'example' => '08098765432',
                'notes' => 'Optional.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function headings(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array<int, string>
     */
    public static function requiredHeadings(): array
    {
        return array_keys(array_filter(
            self::all(),
            fn (array $field): bool => $field['required']
        ));
    }

    /**
     * @return array<int, string>
     */
    public static function optionalHeadings(): array
    {
        return array_keys(array_filter(
            self::all(),
            fn (array $field): bool => ! $field['required']
        ));
    }

    /**
     * The sample row the template ships.
     *
     * @return array<string, string>
     */
    public static function exampleRow(): array
    {
        return array_map(
            fn (array $field): string => $field['example'],
            self::all()
        );
    }

    /**
     * Map a header from an uploaded file onto the column it feeds.
     *
     * Matching is case- and space-insensitive, because a header typed by hand
     * as "Full Name" or "Phone Number" should not silently produce an empty
     * column.
     */
    public static function resolveHeader(string $header): ?string
    {
        $normalised = self::normaliseHeader($header);

        foreach (self::all() as $name => $field) {
            if (self::normaliseHeader($name) === $normalised) {
                return $name;
            }

            foreach ($field['aliases'] as $alias) {
                if (self::normaliseHeader($alias) === $normalised) {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Headers that would be read but thrown away.
     *
     * Surfaced on the import screen, because a misspelled column is otherwise
     * invisible: the row imports and the data is simply absent.
     *
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    public static function unrecognised(array $headers): array
    {
        $unknown = [];

        foreach ($headers as $header) {
            if (trim((string) $header) === '') {
                continue;
            }

            if (self::resolveHeader((string) $header) === null) {
                $unknown[] = trim((string) $header);
            }
        }

        return $unknown;
    }

    /**
     * How many of a file's headings the importer actually reads.
     *
     * Used to tell "you sent the wrong sheet" apart from "you sent a file with
     * nothing in it". Both import zero guests, but only one is a mistake worth
     * interrupting the user about.
     *
     * @param  array<int, string>  $headers
     */
    public static function recognisedCount(array $headers): int
    {
        return count(array_filter(array_map(
            static fn ($header): ?string => self::resolveHeader((string) $header),
            $headers
        )));
    }

    /**
     * Required columns that a file's header row does not contain.
     *
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    public static function missingRequired(array $headers): array
    {
        $present = array_values(array_filter(array_map(
            static fn ($header): ?string => self::resolveHeader((string) $header),
            $headers
        )));

        return array_values(array_diff(self::requiredHeadings(), $present));
    }

    protected static function normaliseHeader(string $header): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', $header) ?? '');
    }
}
