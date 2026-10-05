<?php

namespace Modules\Frontdeskcrm\Support;

/**
 * Spellings of a phone number that could all mean the same guest.
 *
 * `guests.contact_number` is UNIQUE, and it holds whatever was typed: the
 * directory has always accepted both the local `080…` form and the
 * international `+234…` form, and `RegistrationController` has always matched
 * on both. So a single canonical spelling cannot be assumed when looking a
 * guest up, and this class deliberately produces a *set* of candidates instead
 * of rewriting what is stored.
 *
 * The alternative - normalising the column to one form - is not available
 * without a backfill of existing rows, and it would change what the directory
 * displays for every guest already on file.
 *
 * Known limit: a number already stored *with* separators (a legacy row like
 * `+234 801 111 2222`) is not matched, because comparing digits inside SQL
 * would need a database-specific function. Rows written through the registration
 * flows have always been stripped of separators first.
 */
final class GuestPhoneNumber
{
    /**
     * Separator characters that carry no meaning and vary freely between files.
     */
    private const SEPARATORS = '/[\s\-\(\)\.]+/';

    /**
     * Every stored spelling that could be the same guest as this input.
     *
     * The cleaned input is always included, so this can only ever widen a
     * lookup. It never narrows one.
     *
     * @return array<int, string>
     */
    public static function spellings(?string $input): array
    {
        $clean = self::clean($input);

        if ($clean === null) {
            return [];
        }

        $candidates = [$clean, self::digits($clean)];

        // 0801... is the same number as +234801... and as 234801..., and which
        // one a guest was typed in depends on who typed it.
        if (preg_match('/^0(\d{9,10})$/', $clean, $matches) === 1) {
            $candidates[] = '+234'.$matches[1];
            $candidates[] = '234'.$matches[1];
        }

        // `array_unique` below collapses the case where the input was already
        // one of the generated forms.

        if (preg_match('/^\+?234(\d{9,10})$/', $clean, $matches) === 1) {
            // Also offered with the plus, because the column holds whichever
            // form was typed and the plus is not part of any unique index.
            $candidates[] = '+234'.$matches[1];
            $candidates[] = '0'.$matches[1];
        }

        return array_values(array_unique(array_filter($candidates, fn (string $c): bool => $c !== '')));
    }

    /**
     * Whether two numbers are the same once formatting is ignored.
     *
     * Used for naming the match in the rejection report, so "already on file"
     * always says which column actually collided.
     */
    public static function matches(?string $stored, ?string $input): bool
    {
        $storedDigits = self::digits($stored);
        $inputDigits = self::digits($input);

        return $storedDigits !== '' && $storedDigits === $inputDigits;
    }

    /**
     * Digits only, for comparing two numbers for equivalence.
     */
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', $value ?? '') ?? '';
    }

    /**
     * The input with separators removed, or null when there is nothing there.
     */
    public static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace(self::SEPARATORS, '', trim($value)) ?? '';

        return $clean === '' ? null : $clean;
    }
}
