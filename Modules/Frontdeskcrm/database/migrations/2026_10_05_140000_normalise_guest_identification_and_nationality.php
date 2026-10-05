<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestNationality;

/**
 * Normalise the guest identification type and nationality to their canonical forms.
 *
 * Two columns, two separate problems, both caused by free text written by
 * disagreeing flows:
 *
 * `identification_type` held at least six spellings of the same few documents.
 * The front-desk form wrote `NIN` / `Passport` / `Driver's License`; the public
 * booking form wrote `NIN` / `Drivers License` (no apostrophe) / `Voters Card`;
 * the walk-in and profile forms wrote the longer `National ID (NIN)` and
 * `International Passport`. Because the ID-number format check compared against
 * raw literals, the long spellings also skipped the validation entirely.
 *
 * `nationality` mixed a country with its demonym: the registration form offered
 * country names, the walk-in form defaulted to `Nigerian`, the profile form
 * offered a short list containing `USA` and `UK`, and the import guide's example
 * was a demonym.
 *
 * Both columns were offered as `<select>` on at least one surface, so a value
 * stored by one flow rendered blank on another, and on the profile form saving
 * that blank wrote an empty string over a good value.
 *
 * As with the gender and title migrations this is deliberately narrow: only
 * values that resolve to a known identification type are rewritten. An
 * unrecognised identification type is left untouched, because filing a document
 * this code has no name for under "Other" would misdescribe what the guest
 * actually holds. Nationality is the opposite case - an unrecognised country is
 * still a real answer, so it is passed through trimmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->normaliseIdentificationType();
        $this->normaliseNationality();
    }

    public function down(): void
    {
        // Not reversible by design, for the reasons given in the class comment.
    }

    protected function normaliseIdentificationType(): void
    {
        foreach (['guests', 'registrations'] as $table) {
            DB::table($table)
                ->whereNotNull('identification_type')
                ->where('identification_type', '!=', '')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $canonical = GuestIdentificationType::normalise($row->identification_type);

                        // null means "not a document type this code knows" - leave
                        // it alone rather than losing the guest's answer.
                        if ($canonical === null || $canonical === $row->identification_type) {
                            continue;
                        }

                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['identification_type' => $canonical]);
                    }
                });
        }
    }

    protected function normaliseNationality(): void
    {
        foreach (['guests', 'registrations'] as $table) {
            DB::table($table)
                ->whereNotNull('nationality')
                ->where('nationality', '!=', '')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $canonical = GuestNationality::normalise($row->nationality);

                        if ($canonical === $row->nationality) {
                            continue;
                        }

                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['nationality' => $canonical]);
                    }
                });
        }
    }
};
