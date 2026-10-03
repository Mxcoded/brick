<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Exceptions\FiscalPeriodLockedException;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\JournalEntryService;
use Tests\TestCase;

class JournalEntryReversalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        JournalEntry::query()->delete();
        FiscalYear::query()->delete();
    }

    protected function makeEntry(string $date = '2026-06-15', string $number = 'JE-TEST-0001'): JournalEntry
    {
        $cash = ChartOfAccount::where('code', '1100')->value('id');
        $revenue = ChartOfAccount::where('code', '4900')->value('id');

        $entry = JournalEntry::create([
            'entry_number' => $number,
            'date' => $date,
            'description' => 'Room service sale',
            'reference_type' => 'Sale',
            'reference_id' => 42,
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash, 'debit' => 500, 'credit' => 0],
            ['account_id' => $revenue, 'debit' => 0, 'credit' => 500],
        ]);

        return $entry;
    }

    public function test_reversal_creates_a_mirror_image_posted_entry(): void
    {
        $original = $this->makeEntry();

        $reversal = app(JournalEntryService::class)->reverse($original, '2026-06-16', 'Guest complaint');

        $this->assertSame('posted', $reversal->status);
        $this->assertNotNull($reversal->reverses_id);
        $this->assertTrue($reversal->isReversal());
        $this->assertSame('JE-TEST-0001-REV', $reversal->entry_number);
        $this->assertStringContainsString('Reversal of JE-TEST-0001', $reversal->description);
        $this->assertStringContainsString('Guest complaint', $reversal->description);
        $this->assertTrue($reversal->isBalanced());
    }

    public function test_reversal_swaps_debits_and_credits(): void
    {
        $original = $this->makeEntry();
        $reversal = app(JournalEntryService::class)->reverse($original, '2026-06-16');

        $originalLines = $original->load('lines')->lines->mapWithKeys(
            fn ($l) => [$l->account_id => (float) $l->debit]
        );
        $reversalLines = $reversal->load('lines')->lines->mapWithKeys(
            fn ($l) => [$l->account_id => (float) $l->credit]
        );

        $this->assertSame($originalLines->all(), $reversalLines->all());
        $this->assertSame(500.0, (float) $reversal->lines->sum('credit'));
    }

    public function test_original_entry_is_left_untouched(): void
    {
        $original = $this->makeEntry();

        app(JournalEntryService::class)->reverse($original, '2026-06-16');

        $original->refresh();

        $this->assertSame('posted', $original->status);
        $this->assertTrue($original->isReversed());
        $this->assertSame(2, $original->lines()->count());
    }

    public function test_reversal_defaults_to_the_original_date(): void
    {
        $original = $this->makeEntry('2026-06-15');

        $reversal = app(JournalEntryService::class)->reverse($original);

        $this->assertSame('2026-06-15', $reversal->date->toDateString());
    }

    public function test_an_entry_cannot_be_reversed_twice(): void
    {
        $original = $this->makeEntry();
        app(JournalEntryService::class)->reverse($original, '2026-06-16');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already been reversed');

        app(JournalEntryService::class)->reverse($original->fresh(), '2026-06-17');
    }

    public function test_a_draft_cannot_be_reversed(): void
    {
        $original = $this->makeEntry();
        $original->update(['status' => 'draft']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only posted journal entries');

        app(JournalEntryService::class)->reverse($original, '2026-06-16');
    }

    public function test_reversal_into_a_locked_period_is_blocked(): void
    {
        $original = $this->makeEntry('2026-06-15');

        $year = FiscalYear::create([
            'name' => 'FY2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $year->generateMonthlyPeriods();
        $year->periodFor('2026-07-15')->lock();

        $this->expectException(FiscalPeriodLockedException::class);

        app(JournalEntryService::class)->reverse($original, '2026-07-15');
    }

    public function test_nothing_is_written_when_the_reversal_fails(): void
    {
        $original = $this->makeEntry();
        $before = JournalEntry::count();

        try {
            $year = FiscalYear::create([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
            ]);
            $year->generateMonthlyPeriods();
            $year->periodFor('2026-06-15')->lock();

            app(JournalEntryService::class)->reverse($original, '2026-06-16');
        } catch (FiscalPeriodLockedException) {
            // expected
        }

        $this->assertSame($before, JournalEntry::count());
    }
}
