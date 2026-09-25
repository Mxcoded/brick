<?php

namespace Modules\Contracts\Services;

use Modules\Contracts\Models\Agreement;

class AgreementNumberGenerator
{
    /**
     * Generate the next agreement number: AGR-YYYY-0001.
     */
    public function next(?int $year = null): string
    {
        $year = $year ?? now()->year;

        $prefix = "AGR-{$year}-";

        $max = Agreement::query()
            ->where('agreement_number', 'like', $prefix.'%')
            ->get()
            ->map(fn (Agreement $agreement) => (int) substr($agreement->agreement_number, strrpos($agreement->agreement_number, '-') + 1))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }
}
