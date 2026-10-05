<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Frontdeskcrm\Support\GuestImportFields;

class GuestImportInstructionsSheet implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'Instructions';
    }

    /**
     * Generated from GuestImportFields rather than written by hand.
     *
     * The hand-written version marked `phone` optional while the column is
     * NOT NULL, so a file built by following it lost every row without a phone
     * number - and it still described duplicate detection as matching on
     * `full_name`, which is not unique and not what the importer does.
     */
    public function array(): array
    {
        $rows = [['Column', 'Required', 'Example', 'Notes']];

        foreach (GuestImportFields::all() as $name => $field) {
            $aliases = $field['aliases'] ? ' Also accepted as: '.implode(', ', $field['aliases']).'.' : '';

            $rows[] = [
                $name,
                $field['required'] ? 'Yes' : 'No',
                $field['example'],
                $field['notes'].$aliases,
            ];
        }

        return array_merge($rows, [
            [],
            ['IMPORTANT', '', '', 'Keep the header row exactly as listed above. Lower case, no spaces.'],
            ['IMPORTANT', '', '', 'Headings are matched loosely, so "Full Name" or "phone number" still work.'],
            ['IMPORTANT', '', '', 'A row missing a required column is skipped and listed on screen with the reason; the rest of the file still imports.'],
            ['IMPORTANT', '', '', 'A guest already on file, matched on phone number or email, is skipped and listed.'],
            ['IMPORTANT', '', '', 'Accepted file formats: .xlsx, .xls, .csv (max 5MB).'],
            ['IMPORTANT', '', '', 'Fill in the "Guest Template" sheet, save, then upload on the Import Guests page.'],
        ]);
    }
}
