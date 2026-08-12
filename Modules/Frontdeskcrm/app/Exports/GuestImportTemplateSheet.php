<?php

namespace Modules\Frontdeskcrm\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GuestImportTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Guest Template';
    }

    public function headings(): array
    {
        return [
            'full_name',
            'title',
            'email',
            'phone',
            'nationality',
            'gender',
            'birthday',
            'occupation',
            'company',
            'address',
            'city',
            'state',
            'zip_code',
            'identification_type',
            'identification_number',
            'emergency_name',
            'emergency_relationship',
            'emergency_phone',
        ];
    }

    public function collection(): Collection
    {
        return collect([
            [
                'full_name' => 'John Doe',
                'title' => 'Mr',
                'email' => 'john.doe@example.com',
                'phone' => '08012345678',
                'nationality' => 'Nigerian',
                'gender' => 'Male',
                'birthday' => '1990-01-15',
                'occupation' => 'Software Engineer',
                'company' => 'Acme Ltd',
                'address' => '24 Jose Marti Crescent, Asokoro',
                'city' => 'Abuja',
                'state' => 'FCT',
                'zip_code' => '900001',
                'identification_type' => 'NIN',
                'identification_number' => '12345678901',
                'emergency_name' => 'Jane Doe',
                'emergency_relationship' => 'Spouse',
                'emergency_phone' => '08098765432',
            ],
        ]);
    }
}
