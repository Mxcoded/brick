<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Support\GuestTitle;

/**
 * Normalise guests.title and registrations.title to the canonical honorific.
 *
 * Both columns are free text and were written by flows that disagreed on the
 * period: the front-desk guest form, the Filament resource and the import guide
 * all wrote `Mr` / `Mrs` / `Ms` / `Dr` / `Prof`, while the registration and
 * profile forms wrote `Mr.` / `Mrs.` / `Ms.` / `Dr.`. Nothing validated the
 * difference, so a guest's honorific on file depended on which screen recorded
 * them.
 *
 * That mattered because every one of those inputs was a `<select>`. A title
 * stored without the period matched no option on the forms that expected one,
 * rendered blank, and on the guest profile form saving that blank overwrote a
 * good value with an empty string.
 *
 * Both the master record and the registration snapshot are rewritten: the
 * snapshot is a copy of the same attribute, so leaving the old spelling there
 * would keep the two disagreeing for anyone reading the registration.
 *
 * As with the gender migration, this is deliberately narrow. Only values that
 * resolve to a known title are rewritten; anything else is left for a human
 * rather than being nulled out.
 */
return new class extends Migration
{
    /**
     * The tables holding a guest honorific, and the column on each.
     *
     * @var array<string, string>
     */
    private const COLUMNS = [
        'guests' => 'title',
        'registrations' => 'title',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $column): void {
                    foreach ($rows as $row) {
                        $canonical = GuestTitle::normalise($row->{$column});

                        if ($canonical === null || $canonical === $row->{$column}) {
                            continue;
                        }

                        DB::table($table)
                            ->where('id', $row->id)
                            ->update([$column => $canonical]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Not reversible by design. The previous state was a mixture of spellings
        // with no record of which flow wrote each row, so any rollback would be a
        // guess - and a wrong guess reintroduces the blanking bug.
    }
};
