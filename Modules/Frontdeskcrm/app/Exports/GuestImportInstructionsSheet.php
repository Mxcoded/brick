<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class GuestImportInstructionsSheet implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'Instructions';
    }

    public function array(): array
    {
        return [
            ['Column', 'Required', 'Notes'],
            ['full_name', 'Yes', "Guest's full name. Duplicates (matched on full_name or email) are skipped."],
            ['title', 'No', 'Mr, Mrs, Ms, Dr'],
            ['email', 'No', 'Used for the duplicate check. Must be a valid email address if provided.'],
            ['phone', 'No', 'Phone number. The column may also be named contact_number.'],
            ['nationality', 'No', ''],
            ['gender', 'No', 'Male, Female, Other'],
            ['birthday', 'No', 'YYYY-MM-DD format (e.g. 1990-01-15). May also be named date_of_birth.'],
            ['occupation', 'No', ''],
            ['company', 'No', 'May also be named company_name.'],
            ['address', 'No', 'Home address. May also be named home_address.'],
            ['city', 'No', ''],
            ['state', 'No', ''],
            ['zip_code', 'No', 'May also be named zip.'],
            ['identification_type', 'No', 'e.g. NIN, BVN, Passport, Driver Licence. May also be named id_type.'],
            ['identification_number', 'No', 'May also be named id_number.'],
            ['emergency_name', 'No', 'May also be named emergency_contact_name.'],
            ['emergency_relationship', 'No', ''],
            ['emergency_phone', 'No', 'May also be named emergency_contact.'],
            [],
            ['IMPORTANT', '', 'Keep the header row exactly as listed above.'],
            ['IMPORTANT', '', 'Accepted file formats: .xlsx, .xls, .csv (max 5MB).'],
            ['IMPORTANT', '', 'Fill in the "Guest Template" sheet and save, then upload on the Import Guests page.'],
        ];
    }
}
