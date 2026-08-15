<?php

namespace Modules\Frontdeskcrm\Exports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Frontdeskcrm\Models\Guest;

abstract class BaseGuestsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $imported = 0;

    protected $skipped = 0;

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
    {
        $email = ! empty($row['email']) ? $row['email'] : null;

        $exists = Guest::where('full_name', $row['full_name'])
            ->when($email, fn ($q) => $q->orWhere('email', $email))
            ->exists();

        if ($exists) {
            $this->skipped++;

            return null;
        }

        $this->imported++;

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

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}