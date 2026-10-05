<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * The one vocabulary for a guest's identification type, and the number format
 * each type implies.
 *
 * `guests.identification_type` is free text, and at least six surfaces wrote it
 * with a different spelling of the same concept:
 *
 *   front-desk guest form      NIN, Passport, Driver's License, Voter's Card
 *   walk-in registration form  International Passport, National ID (NIN), ...
 *   public booking form        International Passport, NIN, Drivers License, ...
 *   guest profile form         International Passport, National ID (NIN), ...
 *   import guide               NIN, BVN, Passport, Driver Licence
 *   Filament helper text       NIN, Passport, Driver's Licence, Voter's Card
 *
 * Two of those differ only by an apostrophe ("Driver's License" vs "Drivers
 * License"), and the two NIN spellings differ substantially. Two consequences,
 * both silent:
 *
 * 1. Every surface offered a `<select>`, so a guest stored by one surface
 *    matched no option on another and rendered blank. Saving the form then
 *    wrote the empty string over a good value.
 *
 * 2. The public booking form validated the ID number by comparing the type
 *    against literals, so "National ID (NIN)" - offered by the walk-in and
 *    profile forms - skipped the 11-digit NIN check entirely, as did
 *    "Driver's License" for the 3+12+1 check. An invalid ID number passed
 *    validation depending on which screen the guest used.
 *
 * Canonical values are the readable labels, because these are picked by staff
 * from a dropdown rather than typed into a spreadsheet. `numberRule()` owns the
 * format check so it is keyed on the canonical value and cannot be bypassed by
 * arriving under a different spelling.
 */
final class GuestIdentificationType
{
    public const PASSPORT = 'International Passport';

    public const NIN = 'National ID (NIN)';

    public const DRIVERS_LICENCE = "Driver's License";

    public const VOTERS_CARD = "Voter's Card";

    public const BVN = 'Bank Verification Number (BVN)';

    public const OTHER = 'Other';

    /**
     * Every spelling seen in the wild, folded onto its canonical value.
     *
     * Keyed by a punctuation-insensitive form of the input, so "Driver's
     * License", "drivers licence" and "DriversLicense" all land together.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'passport' => self::PASSPORT,
        'internationalpassport' => self::PASSPORT,
        'intlpassport' => self::PASSPORT,

        'nin' => self::NIN,
        'nationalid' => self::NIN,
        'nationalidnin' => self::NIN,
        'nationalidentificationnumber' => self::NIN,

        'driverslicense' => self::DRIVERS_LICENCE,
        'driverlicence' => self::DRIVERS_LICENCE,
        'driverslicence' => self::DRIVERS_LICENCE,
        'driverlicenses' => self::DRIVERS_LICENCE,
        'driverslicenses' => self::DRIVERS_LICENCE,
        'drivinglicence' => self::DRIVERS_LICENCE,
        'dl' => self::DRIVERS_LICENCE,

        'voterscard' => self::VOTERS_CARD,
        'voter' => self::VOTERS_CARD,
        'voters' => self::VOTERS_CARD,

        'bvn' => self::BVN,
        'bankverificationnumber' => self::BVN,
        'bankverificationnumberbvn' => self::BVN,

        'other' => self::OTHER,
        'othergovtid' => self::OTHER,
        'othergovernmentid' => self::OTHER,
    ];

    /**
     * Canonical value => display label, for selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::PASSPORT => 'International Passport',
            self::NIN => 'National ID (NIN)',
            self::DRIVERS_LICENCE => "Driver's License",
            self::VOTERS_CARD => "Voter's Card",
            self::BVN => 'Bank Verification Number (BVN)',
            self::OTHER => 'Other',
        ];
    }

    /**
     * The canonical values, for validation.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    /**
     * Spellings accepted on input but never stored.
     *
     * The canonical list alone would reject a draft or existing guest recorded as
     * "NIN" or "Drivers License", and `normalise()` folds those onto the
     * canonical value before storing.
     *
     * @return array<int, string>
     */
    public static function acceptedValues(): array
    {
        return [
            'Passport', 'International Passport', 'Intl Passport',
            'NIN', 'National ID', 'National ID (NIN)', 'National Identification Number',
            'Drivers License', "Driver's License", 'Driver Licence', "Driver's Licence",
            'Drivers Licence', 'Driving Licence', 'DL',
            'Voters Card', "Voter's Card", 'Voter',
            'BVN', 'Bank Verification Number', 'Bank Verification Number (BVN)',
            'Other', 'Other Govt ID', 'Other Government ID',
        ];
    }

    /**
     * Reduce any known spelling to its canonical value.
     *
     * Returns null for blank input, and null for a value this class does not
     * recognise. A guest's ID type is a legal identity document, and silently
     * filing an unrecognised value under "Other" would misdescribe what they
     * hold - so unknown values are left for a human instead.
     */
    public static function normalise(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return self::ALIASES[self::fold($value)] ?? null;
    }

    /**
     * The number a guest holding this type must supply, or null when the type
     * imposes no format.
     *
     * Checked against the *normalised* type, so the format is enforced no
     * matter which surface's spelling the value arrived in.
     */
    public static function numberRule(mixed $type): ?string
    {
        return match (self::normalise($type)) {
            self::NIN => 'NIN must be exactly 11 digits (e.g., 12345678901).',
            self::PASSPORT => 'Passport number must start with a letter followed by 7-9 digits (e.g., A01234567).',
            self::DRIVERS_LICENCE => "Driver's License must be 3 letters + 12 digits + 1 letter (e.g., ABC123456789012X).",
            self::VOTERS_CARD => "Voter's Card number must be 19 digits.",
            default => null,
        };
    }

    /**
     * Whether an ID number satisfies the format its type requires.
     */
    public static function numberIsValid(mixed $type, mixed $number): bool
    {
        $number = trim((string) $number);

        if ($number === '') {
            return false;
        }

        return match (self::normalise($type)) {
            self::NIN => preg_match('/^\d{11}$/', $number) === 1,
            self::PASSPORT => preg_match('/^[A-Za-z]\d{7,9}$/', $number) === 1,
            self::DRIVERS_LICENCE => preg_match('/^[A-Za-z]{3}\d{12}[A-Za-z]$/', $number) === 1,
            self::VOTERS_CARD => preg_match('/^\d{19}$/', $number) === 1,
            // BVN is 11 digits too, but it is a bank credential rather than a
            // national identity document, so it is not constrained here.
            default => true,
        };
    }

    public static function matches(mixed $stored, mixed $input): bool
    {
        $stored = self::normalise($stored);

        return $stored !== null && $stored === self::normalise($input);
    }

    /**
     * Lowercase and strip everything that is not a letter or a digit, so
     * "Driver's License", "driver licence" and "DriversLicense" fold together.
     */
    private static function fold(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?? '');
    }
}
