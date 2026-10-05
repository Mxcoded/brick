<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * The one vocabulary for a guest's gender.
 *
 * `guests` is the databank and `registrations` is the process record that
 * snapshots a guest's simple attributes, so the same three values are written
 * to both tables. They were not written the same way:
 *
 *   - the directory form and the importer wrote `Male` / `Female` / `Other`
 *   - the public booking form wrote `male` / `female` / `other`
 *     (`in:male,female,other` at WebsiteController)
 *
 * `registrations.gender` is `enum('male','female','other')` and MySQL compares
 * enum members case-insensitively, so writing `Male` there does not fail. It is
 * silently coerced to `male`, which left the guest row and the registration row
 * holding two different spellings of the same person's attribute with nothing
 * logged anywhere.
 *
 * The damage showed up wherever the value was compared as a string rather than
 * coerced by the database. `RegistrationController` derived the honorific with
 * `$guest->gender == 'Male'`, so every guest who booked through the website
 * arrived lowercase, failed the comparison, and had "Ms." prefilled on the
 * registration form. No error, just a wrong name on a guest's paperwork.
 *
 * Canonical form is Title case, because that is what the directory displays and
 * what the seeder writes. `registrations` keeps its lowercase enum; MySQL
 * coerces on the way in, and reads back lowercase either way.
 */
final class GuestGender
{
    public const MALE = 'Male';

    public const FEMALE = 'Female';

    public const OTHER = 'Other';

    /**
     * The canonical values, keyed by value, for form selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        ];
    }

    /**
     * Reduce any input to one of the canonical values, or null when blank.
     *
     * Accepts the lowercase forms the website writes, the Title case forms the
     * directory and importer write, and the single-letter abbreviations that
     * turn up in spreadsheets. Anything unrecognised becomes "Other" rather than
     * being stored verbatim, because a free-text value here cannot be compared
     * against anything.
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

        return match (mb_strtolower($value)) {
            'male', 'm' => self::MALE,
            'female', 'f' => self::FEMALE,
            default => self::OTHER,
        };
    }

    /**
     * The values `registrations.gender` stores, for validation and selects on
     * the registration form.
     *
     * Lowercase, because that is what the enum holds. The registration form and
     * the public booking form both post these.
     *
     * @return array<int, string>
     */
    public static function registrationValues(): array
    {
        return array_map('mb_strtolower', array_keys(self::options()));
    }

    /**
     * Map any spelling onto the lowercase member the enum stores.
     *
     * MySQL would coerce a Title-cased value anyway, but passing the member it
     * stores means the row and the value the application compares against
     * always agree, rather than the coercion being invisible.
     */
    public static function forRegistration(mixed $value): ?string
    {
        $canonical = self::normalise($value);

        return $canonical === null ? null : mb_strtolower($canonical);
    }

    /**
     * A guest's honorific, or null when there is not enough to guess.
     *
     * Deliberately returns null rather than defaulting to "Ms.": guessing wrong
     * puts the wrong name on a guest's registration, and the field is editable.
     */
    public static function honorific(mixed $value): ?string
    {
        return match (self::normalise($value)) {
            self::MALE => 'Mr.',
            self::FEMALE => 'Ms.',
            default => null,
        };
    }

    /**
     * Whether two spellings mean the same gender.
     *
     * For matching a value already on file against one just submitted, where the
     * stored spelling may predate this class.
     */
    public static function matches(mixed $stored, mixed $input): bool
    {
        $stored = self::normalise($stored);

        return $stored !== null && $stored === self::normalise($input);
    }
}
