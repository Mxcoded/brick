<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;

/**
 * Reads the heading row and nothing else.
 *
 * The import page shows a verdict on the file as soon as it is chosen, so this
 * deliberately stops after one row: a 5 MB guest list would otherwise be fully
 * parsed just to answer "is the phone column spelled right?".
 *
 * Deliberately no WithHeadingRow. The point is to see the headings as the user
 * typed them, not as the importer would reinterpret them, since the misspelling
 * being reported is exactly what WithHeadingRow would hide.
 */
class GuestImportHeaderReader implements ToModel, WithLimit
{
    public function limit(): int
    {
        return 1;
    }

    public function model(array $row): array
    {
        // Nothing is written: this import exists only to hand the heading row
        // back to the page.
        return [];
    }
}
