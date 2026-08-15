<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Modules\Frontdeskcrm\Models\Guest;

class GuestsExcelImport extends GuestsImport implements WithMultipleSheets
{
    /**
     * Only the first sheet (Guest Template) is imported.
     * Returns a fresh BaseGuestsImport instance to avoid circular recursion
     * in maatwebsite/excel's requiresStyleInformation().
     * The sheet instance mutates THIS object's counters via reference.
     */
    public function sheets(): array
    {
        $parent = $this;
        return [
            'Guest Template' => new class($parent) extends BaseGuestsImport {
                private $parent;

                public function __construct(BaseGuestsImport $parent)
                {
                    $this->parent = $parent;
                }

                // Mutate parent's counters directly so controller sees correct totals
                protected function incrementImported(): void
                {
                    $this->parent->imported++;
                }

                protected function incrementSkipped(): void
                {
                    $this->parent->skipped++;
                }

                public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
                {
                    $email = ! empty($row['email']) ? $row['email'] : null;

                    $exists = Guest::where('full_name', $row['full_name'])
                        ->when($email, fn ($q) => $q->orWhere('email', $email))
                        ->exists();

                    if ($exists) {
                        $this->incrementSkipped();

                        return null;
                    }

                    $this->incrementImported();

                    return new Guest([
                        'title' => $row['title'] ?? null,
                        'full_name' => $row['full_name'],
                        'email' => $email,
                        'contact_number' => $row['contact_number'] ?? $row['phone'] ?? null,
                        'nationality' => $row['nationality'] ?? null,
                        'identification_type' => $row['identification_type'] ?? $row['id_type'] ?? null,
                        'identification_number' => $row['identification_number'] ?? $row['id_number'] ?? null,
                        'gender' => $row['gender'] ?? null,
                        'birthday' => $row['birthday'] ?? $row['date_of_birth'] ?? null,
                        'occupation' => $row['occupation'] ?? null,
                        'company_name' => $row['company'] ?? $row['company_name'] ?? null,
                        'home_address' => $row['address'] ?? $row['home_address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'state' => $row['state'] ?? null,
                        'zip_code' => $row['zip_code'] ?? $row['zip'] ?? null,
                        'emergency_name' => $row['emergency_name'] ?? $row['emergency_contact_name'] ?? null,
                        'emergency_relationship' => $row['emergency_relationship'] ?? null,
                        'emergency_contact' => $row['emergency_contact'] ?? $row['emergency_phone'] ?? null,
                    ]);
                }
            },
        ];
    }
}