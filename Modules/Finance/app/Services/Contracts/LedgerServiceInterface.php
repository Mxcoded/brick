<?php

namespace Modules\Finance\Services\Contracts;

use Modules\Finance\Models\JournalEntry;

interface LedgerServiceInterface
{
    /**
     * Returns true when the sum of debits equals the sum of credits.
     */
    public function isBalanced(array $lines): bool;

    /**
     * Posts a balanced double-entry journal entry.
     *
     * @param  array[]  $lines  Each line: ['account_id' => int, 'debit' => float, 'credit' => float, 'cost_center' => ?string, 'description' => ?string]
     *                          or ['account_code' => string, ...] to resolve the account by GL code.
     * @param  array  $meta  Optional: ['entry_number', 'date', 'reference_type', 'reference_id', 'description']
     */
    public function post(array $lines, array $meta = []): JournalEntry;
}
