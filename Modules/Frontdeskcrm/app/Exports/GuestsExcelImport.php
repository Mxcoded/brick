<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GuestsExcelImport extends GuestsImport implements WithMultipleSheets
{
    /**
     * Only the first sheet is imported; the guide and template sheets are for
     * the user's benefit, not data.
     *
     * The row work itself is inherited unchanged from BaseGuestsImport. This
     * used to hand-roll a second implementation inline, which had drifted from
     * the single-sheet path: it counted a row as imported before the save had
     * succeeded, matched duplicates on `full_name` (which is not unique, so it
     * dropped every second guest with a shared name), and stored `gender`
     * verbatim instead of normalising it. Delegating keeps one implementation,
     * so the CSV and Excel paths cannot report different results.
     *
     * A separate sheet instance is still needed: returning `$this` makes
     * maatwebsite/excel recurse through requiresStyleInformation(). It is a
     * bare subclass that forwards both counters to the object the controller
     * holds, which is what makes the reported totals correct.
     */
    public function sheets(): array
    {
        return [
            new GuestSheetProxy($this),
        ];
    }
}
