<?php

namespace Modules\Website\Imports;

use Modules\Website\Models\NewsletterSubscriber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Str;

class NewsletterSubscriberImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected int $imported = 0;
    protected int $skipped = 0;
    protected int $reactivated = 0;

    public function model(array $row)
    {
        $email = strtolower(trim($row['email']));
        $name = isset($row['name']) ? trim($row['name']) : null;

        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if (!$existing->is_active) {
                $existing->update([
                    'name' => $name ?? $existing->name,
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
                $this->reactivated++;
            } else {
                $existing->update([
                    'name' => $name ?? $existing->name,
                ]);
                $this->skipped++;
            }
            return null;
        }

        $this->imported++;

        return new NewsletterSubscriber([
            'name' => $name,
            'email' => $email,
            'unsubscribe_token' => Str::random(64),
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns',
            'name' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'email.required' => 'Email is required for each row.',
            'email.email' => 'The email ":input" is not a valid email address.',
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

    public function getReactivatedCount(): int
    {
        return $this->reactivated;
    }
}
