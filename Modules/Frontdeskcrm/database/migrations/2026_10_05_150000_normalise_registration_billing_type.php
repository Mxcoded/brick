<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalise registrations.billing_type to the canonical vocabulary.
 *
 * The column is free text and two screens disagreed on the name for "each
 * guest pays their own way". The walk-in form offered `split`, while the
 * finalize screen offered `individual`, and the two request classes validated
 * against different sets (`StoreRegistrationRequest` accepted
 * `consolidate,split`, `FinalizeRegistrationRequest` accepted
 * `consolidate,individual`).
 *
 * That mismatch was silent. `finalize()` branches on `=== 'individual'`, so a
 * walk-in created with "Split by Guest" failed the comparison and fell through
 * to the consolidate branch: members had their `total_amount` zeroed and the
 * lead was billed the whole party, with no error and no warning. The finalize
 * select then found no option matching `split`, fell back to its first option
 * and displayed "Consolidate on Group Lead" as though that had been chosen.
 *
 * `split` and `individual` mean the same thing in both places ("Split by
 * Guest" / "Individual Billing (Each Pays Own)"), so `individual` is kept as
 * the single name the code already branches on.
 *
 * Only the two known values are rewritten. Anything else is left alone rather
 * than being guessed at.
 */
return new class extends Migration
{
    /**
     * Legacy spellings, and the canonical value each maps to.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'split' => 'individual',
    ];

    public function up(): void
    {
        DB::table('registrations')
            ->whereIn('billing_type', array_keys(self::ALIASES))
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('registrations')
                        ->where('id', $row->id)
                        ->update(['billing_type' => self::ALIASES[$row->billing_type]]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible by design. The two spellings were used by different
        // screens with no record of which one a given row came from, so a
        // rollback would be a guess - and a wrong guess puts the silent
        // consolidate-instead-of-split bug straight back.
    }
};
