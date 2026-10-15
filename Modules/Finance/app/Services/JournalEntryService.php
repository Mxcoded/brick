<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\JournalEntry;

class JournalEntryService
{
    public function __construct(
        protected FiscalPeriodService $fiscalPeriods,
    ) {}

    /**
     * Creates the mirror-image entry that cancels an original. The original is
     * never mutated or deleted, so the audit trail stays intact — this is the
     * only supported way to correct a posted entry.
     */
    public function reverse(JournalEntry $original, ?string $date = null, ?string $reason = null): JournalEntry
    {
        if ($original->status !== 'posted') {
            throw new \InvalidArgumentException('Only posted journal entries can be reversed.');
        }

        if ($original->reversal !== null) {
            throw new \InvalidArgumentException('This journal entry has already been reversed.');
        }

        $date ??= $original->date->toDateString();

        $this->fiscalPeriods->assertOpenFor($date);

        return DB::transaction(function () use ($original, $date, $reason): JournalEntry {
            $reversal = JournalEntry::create([
                'entry_number' => $this->nextReversalNumber($original),
                'date' => $date,
                'reference_type' => $original->reference_type,
                'reference_id' => $original->reference_id,
                'description' => trim(sprintf(
                    'Reversal of %s%s',
                    $original->entry_number,
                    $reason ? ": {$reason}" : '',
                )),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $original->load('lines')->lines->each(
                fn ($line) => $reversal->lines()->create([
                    'account_id' => $line->account_id,
                    'party_type' => $line->party_type,
                    'party_id' => $line->party_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'cost_center' => $line->cost_center,
                    'description' => $line->description,
                ])
            );

            $reversal->forceFill([
                'reverses_id' => $original->getKey(),
                'status' => 'posted',
                'posted_at' => now(),
            ])->save();

            return $reversal->fresh();
        });
    }

    /**
     * Entry numbers are unique, so a collision on the natural key falls back to
     * the sequential format rather than blowing up on the unique index.
     */
    protected function nextReversalNumber(JournalEntry $original): string
    {
        $natural = $original->entry_number.'-REV';

        if (! JournalEntry::where('entry_number', $natural)->exists()) {
            return $natural;
        }

        return JournalEntry::nextEntryNumber();
    }
}
