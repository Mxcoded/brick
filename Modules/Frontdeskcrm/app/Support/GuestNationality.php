<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * The one vocabulary for a guest's nationality: the country's name.
 *
 * The column held three different kinds of value at once. The registration form
 * offered country names from the shared country list ("Nigeria", "United
 * Kingdom"). The walk-in form defaulted to a demonym ("Nigerian"). The profile
 * form offered a five-item list mixing a country with two abbreviations
 * ("Nigeria", "USA", "UK"). The import guide's example was a demonym.
 *
 * All of them landed in the same column, so the same person could be recorded
 * three ways. Because the profile form compares with a strict `===` against its
 * own short list, a guest recorded as "Nigerian" or "United Kingdom" matched no
 * option there, rendered blank, and was overwritten with an empty string the
 * next time the guest saved their profile.
 *
 * Canonical form is the country name, matching the shared country list that the
 * registration form uses.
 *
 * Deliberate limit: only variants that are actually in circulation are folded -
 * abbreviations, demonyms and the punctuation variants of the country names.
 * Anything unrecognised is passed through trimmed rather than being discarded,
 * so a nationality this table has never seen is still recorded rather than
 * silently blanked on a guest's profile.
 */
final class GuestNationality
{
    /**
     * Folded spelling => canonical country name.
     *
     * Keys are country names and demonyms with punctuation and spaces removed.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        // Nigeria
        'nigeria' => 'Nigeria',
        'nigerian' => 'Nigeria',
        'nigerians' => 'Nigeria',
        'federalrepublicofnigeria' => 'Nigeria',

        // United Kingdom, and the forms that broke most often
        'unitedkingdom' => 'United Kingdom',
        'unitedkingdomofgreatbritainandnorthernireland' => 'United Kingdom',
        'uk' => 'United Kingdom',
        'greatbritain' => 'United Kingdom',
        'britain' => 'United Kingdom',
        'british' => 'United Kingdom',
        'england' => 'United Kingdom',
        'english' => 'United Kingdom',
        'scotland' => 'United Kingdom',
        'scottish' => 'United Kingdom',
        'wales' => 'United Kingdom',
        'welsh' => 'United Kingdom',
        'northernireland' => 'United Kingdom',

        // United States, and its abbreviations
        'unitedstates' => 'United States',
        'unitedstatesofamerica' => 'United States',
        'usa' => 'United States',
        'us' => 'United States',
        'america' => 'United States',
        'american' => 'United States',
        'americans' => 'United States',

        // Neighbours and common source markets
        'ghana' => 'Ghana',
        'ghanaian' => 'Ghana',
        'kenya' => 'Kenya',
        'kenyan' => 'Kenya',
        'southafrica' => 'South Africa',
        'southafrican' => 'South Africa',
        'india' => 'India',
        'indian' => 'India',
        'canada' => 'Canada',
        'canadian' => 'Canada',
        'ireland' => 'Ireland',
        'irish' => 'Ireland',
        'germany' => 'Germany',
        'german' => 'Germany',
        'france' => 'France',
        'french' => 'France',
        'italy' => 'Italy',
        'italian' => 'Italy',
        'spain' => 'Spain',
        'spanish' => 'Spain',
        'portugal' => 'Portugal',
        'portuguese' => 'Portugal',
        'brazil' => 'Brazil',
        'brazilian' => 'Brazil',
    ];

    /**
     * Reduce a known variant to its canonical country name.
     *
     * Unrecognised values are returned trimmed and unchanged: a nationality not
     * in this table is still a real answer, and blanking it would be worse than
     * storing it verbatim.
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

        return self::ALIASES[self::fold($value)] ?? $value;
    }

    /**
     * Whether two values name the same country.
     */
    public static function matches(mixed $stored, mixed $input): bool
    {
        return self::normalise($stored) !== null
            && self::normalise($stored) === self::normalise($input);
    }

    /**
     * Spellings that fold onto a country name.
     *
     * Used by the profile form so a guest recorded before this class existed is
     * still recognised and shown rather than blanked.
     *
     * @return array<int, string>
     */
    public static function knownSpellings(): array
    {
        return array_keys(self::ALIASES);
    }

    private static function fold(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?? '');
    }
}
