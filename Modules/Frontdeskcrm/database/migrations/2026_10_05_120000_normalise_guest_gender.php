<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Support\GuestGender;

/**
 * Normalise guests.gender to the canonical vocabulary.
 *
 * `guests.gender` is a free-text varchar that was written by several flows
 * that disagreed on casing: the directory form and the importer wrote
 * `Male`/`Female`/`Other`, while the public booking form wrote
 * `male`/`female`/`other`. Nothing rejected the difference, so both spellings
 * are on file.
 *
 * This matters because the value is compared as a string in
 * `RegistrationController` when deriving a guest's honorific. A guest stored
 * lowercase failed the `== 'Male'` test and had "Ms." prefilled on the
 * registration form - a male guest, a wrong title, and no error anywhere.
 *
 * `registrations.gender` is an `enum('male','female','other')` that MySQL
 * already coerces case-insensitively, so those rows need no change.
 *
 * The migration is deliberately narrow. It only rewrites values it can reduce
 * to a known gender, and leaves anything unrecognised alone rather than
 * flattening a human-entered value into "Other" behind the operator's back.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('guests')
            ->whereNotNull('gender')
            ->where('gender', '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($guests): void {
                foreach ($guests as $guest) {
                    $canonical = $this->canonical($guest->gender);

                    if ($canonical === null || $canonical === $guest->gender) {
                        continue;
                    }

                    DB::table('guests')
                        ->where('id', $guest->id)
                        ->update(['gender' => $canonical]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible by design. The previous state was not one value but a
        // mixture of casings, and there is no way to tell which rows were
        // written by which flow. Any "rollback" would be a guess, and a wrong
        // guess here reintroduces the honorific bug.
    }

    /**
     * The canonical value for a stored gender, or null when it is not one this
     * migration is willing to rewrite.
     */
    protected function canonical(string $stored): ?string
    {
        $lowered = mb_strtolower(trim($stored));

        // Only act on values whose meaning is unambiguous. Free text that a
        // person typed by hand is left for a human to look at.
        return match ($lowered) {
            'male', 'm' => GuestGender::MALE,
            'female', 'f' => GuestGender::FEMALE,
            'other' => GuestGender::OTHER,
            default => null,
        };
    }
};
