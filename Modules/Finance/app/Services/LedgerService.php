<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\Contracts\LedgerServiceInterface;

class LedgerService implements LedgerServiceInterface
{
    public function isBalanced(array $lines): bool
    {
        $debit = collect($lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
        $credit = collect($lines)->sum(fn ($line) => (float) ($line['credit'] ?? 0));

        return abs($debit - $credit) < 0.005;
    }

    public function post(array $lines, array $meta = []): JournalEntry
    {
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('A journal entry requires at least two lines.');
        }

        if (! $this->isBalanced($lines)) {
            throw new \InvalidArgumentException('Journal entry is not balanced: total debits must equal total credits.');
        }

        $entry = JournalEntry::create([
            'entry_number' => $meta['entry_number'] ?? $this->nextEntryNumber(),
            'date' => $meta['date'] ?? now()->toDateString(),
            'reference_type' => $meta['reference_type'] ?? null,
            'reference_id' => $meta['reference_id'] ?? null,
            'description' => $meta['description'] ?? null,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $account = isset($line['account_id'])
                ? ChartOfAccount::findOrFail($line['account_id'])
                : ChartOfAccount::where('code', $line['account_code'])->firstOrFail();

            $entry->lines()->create([
                'account_id' => $account->id,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'cost_center' => $line['cost_center'] ?? null,
                'description' => $line['description'] ?? null,
            ]);
        }

        return $entry;
    }

    protected function nextEntryNumber(): string
    {
        $prefix = 'JE-'.now()->format('Ym').'-';
        $last = JournalEntry::where('entry_number', 'like', $prefix.'%')->max('entry_number');
        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
