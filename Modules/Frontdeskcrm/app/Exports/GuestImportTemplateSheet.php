<?php

namespace Modules\Frontdeskcrm\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Frontdeskcrm\Support\GuestImportFields;

class GuestImportTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Guest Template';
    }

    /**
     * Headings and the sample row both come from GuestImportFields, so the
     * template cannot advertise a column the importer does not read.
     */
    public function headings(): array
    {
        return GuestImportFields::headings();
    }

    public function collection(): Collection
    {
        return collect([GuestImportFields::exampleRow()]);
    }
}
