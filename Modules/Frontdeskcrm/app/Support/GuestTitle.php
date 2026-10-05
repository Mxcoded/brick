<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * The one vocabulary for a guest's honorific.
 *
 * Six surfaces offered a title list and no two agreed. The front-desk guest
 * form and the Filament resource used `Mr` / `Mrs` / `Ms` / `Miss` / `Dr` /
 * `Prof` with no periods; the profile form and both registration forms used
 * `Mr.` / `Mrs.` / `Ms.` / `Dr.`; the two legacy guest forms added `Chief`, and
 * the import guide documented a different set again. Nothing validated the
 * value, so all of it landed in the column.
 *
 * Because every one of those is a `<select>`, a title stored by one surface
 * matched no option on another and rendered blank - and on the guest profile
 * form, saving that blank wrote an empty string over a good value.
 *
 * Canonical values carry the period, which is correct in running text and is
 * what the seeders and the generated honorific already wrote. `normalise()`
 * accepts the period-less forms so an existing row still resolves.
 */
final class GuestTitle
{
    public const MR = 'Mr.';

    public const MRS = 'Mrs.';

    public const MS = 'Ms.';

    public const MISS = 'Miss';

    public const DR = 'Dr.';

    public const PROF = 'Prof.';

    public const CHIEF = 'Chief';

    /**
     * Every spelling seen in the wild, folded onto its canonical value.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'mr' => self::MR,
        'mister' => self::MR,
        'mrs' => self::MRS,
        'missus' => self::MRS,
        'ms' => self::MS,
        'miss' => self::MISS,
        'dr' => self::DR,
        'doctor' => self::DR,
        'prof' => self::PROF,
        'professor' => self::PROF,
        'chief' => self::CHIEF,
    ];

    /**
     * Canonical value => display label, for selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::MR => 'Mr.',
            self::MRS => 'Mrs.',
            self::MS => 'Ms.',
            self::MISS => 'Miss',
            self::DR => 'Dr.',
            self::PROF => 'Prof.',
            self::CHIEF => 'Chief',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    /**
     * Spellings accepted on input but never stored.
     *
     * The canonical list alone would reject a form still posting "Mr" or "Dr",
     * and re-saving an existing guest is exactly when those resurface. The value
     * is folded onto its canonical form by `normalise()` before it is stored.
     *
     * @return array<int, string>
     */
    public static function acceptedValues(): array
    {
        return [
            'Mr', 'Mister', 'Mrs', 'Missus', 'Ms', 'Dr', 'Doctor',
            'Prof', 'Professor',
        ];
    }

    /**
     * Reduce any known spelling to its canonical value, or null when blank.
     *
     * An unrecognised title is returned null rather than guessed at: the column
     * is nullable, and inventing a title is worse than leaving it empty.
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
     * The title to prefill for a guest, preferring what is already on file.
     *
     * `GuestGender::honorific()` derives Mr. or Ms. from the gender, but a guest
     * recorded as "Mrs." or "Dr." must keep that. Deriving from gender alone
     * silently downgraded a married woman to "Ms." on every registration.
     */
    public static function forGuest(?string $stored, mixed $gender): ?string
    {
        return self::normalise($stored) ?? GuestGender::honorific($gender);
    }

    public static function matches(mixed $stored, mixed $input): bool
    {
        $stored = self::normalise($stored);

        return $stored !== null && $stored === self::normalise($input);
    }

    private static function fold(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?? '');
    }
}
