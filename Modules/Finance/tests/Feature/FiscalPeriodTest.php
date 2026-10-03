<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Exceptions\FiscalPeriodLockedException;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\FiscalPeriod;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\FiscalPeriodService;
use Modules\Finance\Services\LedgerService;
use Tests\TestCase;

class FiscalPeriodTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        FiscalPeriod::query()->delete();
        FiscalYear::query()->delete();
    }

    protected function makeYear(string $start, string $end, int $months = 12): FiscalYear
    {
        $year = FiscalYear::create([
            'name' => substr($start, 0, 4).'-'.substr($end, 2, 2),
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => true,
        ]);

        $year->generateMonthlyPeriods($months);

        return $year->refresh();
    }

    public function test_generating_monthly_periods_covers_the_whole_year(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');

        $this->assertCount(12, $year->periods);
        $this->assertSame('Jan 2026', $year->periods->first()->name);
        $this->assertSame('2026-01-01', $year->periods->first()->start_date->toDateString());
        $this->assertSame('2026-12-31', $year->periods->last()->end_date->toDateString());
    }

    public function test_periods_are_clipped_to_partial_boundary_months(): void
    {
        $year = $this->makeYear('2026-04-15', '2027-03-10');

        $periods = $year->periods()->orderBy('start_date')->get();

        $this->assertCount(12, $periods);
        $this->assertSame('2026-04-15', $periods->first()->start_date->toDateString());
        $this->assertSame('2026-04-30', $periods->first()->end_date->toDateString());
        $this->assertSame('2027-03-01', $periods->last()->start_date->toDateString());
        $this->assertSame('2027-03-10', $periods->last()->end_date->toDateString());
    }

    public function test_generating_periods_twice_does_not_duplicate(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $year->generateMonthlyPeriods();

        $this->assertCount(12, $year->periods()->get());
    }

    public function test_period_lookup_by_date(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $period = $year->periodFor(now());

        $this->assertNotNull($period);
        $this->assertTrue($period->contains(now()));
    }

    public function test_posting_is_unrestricted_when_no_fiscal_calendar_exists(): void
    {
        $this->assertNull(app(FiscalPeriodService::class)->periodFor('2026-06-15'));
        $this->assertFalse(app(FiscalPeriodService::class)->isLockedFor('2026-06-15'));
    }

    public function test_locking_a_period_blocks_new_postings(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $period = $year->periodFor(now());
        $period->lock();

        $this->assertTrue($period->fresh()->isLocked());
        $this->assertTrue(app(FiscalPeriodService::class)->isLockedFor(now()));

        $this->expectException(FiscalPeriodLockedException::class);

        $this->postEntry(now()->toDateString());
    }

    public function test_unlocking_a_period_allows_postings_again(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $period = $year->periodFor(now());
        $period->lock();
        $period->unlock();

        $this->assertFalse($period->fresh()->isLocked());
        $this->assertNotNull($this->postEntry(now()->toDateString()));
    }

    public function test_locking_only_affects_the_targeted_period(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $year->periodFor('2026-03-10')->lock();

        $this->assertTrue(app(FiscalPeriodService::class)->isLockedFor('2026-03-10'));
        $this->assertFalse(app(FiscalPeriodService::class)->isLockedFor('2026-06-10'));
    }

    public function test_posting_a_draft_into_a_locked_period_is_blocked(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');

        $entry = $this->postEntry(now()->toDateString());
        $entry->update(['status' => 'draft']);

        $year->periodFor(now())->lock();

        $this->expectException(FiscalPeriodLockedException::class);
        $entry->post();
    }

    public function test_closing_a_fiscal_year_locks_all_of_its_periods(): void
    {
        $year = $this->makeYear('2026-01-01', '2026-12-31');
        $year->close();

        $this->assertTrue($year->fresh()->is_closed);
        $this->assertFalse($year->fresh()->is_active);
        $this->assertSame(0, $year->periods()->open()->count());
        $this->assertSame(12, $year->periods()->locked()->count());
    }

    public function test_default_report_range_uses_the_current_period(): void
    {
        $this->assertNull(app(FiscalPeriodService::class)->defaultReportRange());

        $this->makeYear('2026-01-01', '2026-12-31');

        $range = app(FiscalPeriodService::class)->defaultReportRange();

        $this->assertSame(now()->startOfMonth()->toDateString(), $range['from']);
        $this->assertSame(now()->endOfMonth()->toDateString(), $range['to']);
    }

    public function test_default_report_range_falls_back_to_the_active_year(): void
    {
        $this->makeYear('2000-01-01', '2000-12-31');

        $range = app(FiscalPeriodService::class)->defaultReportRange();

        $this->assertSame('2000-01-01', $range['from']);
        $this->assertSame('2000-12-31', $range['to']);
    }

    public function test_current_fiscal_year_resolution(): void
    {
        $this->assertNull(FiscalYear::current());

        $year = $this->makeYear('2026-01-01', '2026-12-31');

        $this->assertTrue($year->is(FiscalYear::current()));
    }

    protected function postEntry(string $date): JournalEntry
    {
        $accounts = ChartOfAccount::query()
            ->whereIn('code', ['1100', '4900'])
            ->pluck('id', 'code');

        return app(LedgerService::class)->post(
            [
                ['account_id' => $accounts['1100'], 'debit' => 100],
                ['account_id' => $accounts['4900'], 'credit' => 100],
            ],
            ['date' => $date, 'entry_number' => 'TEST-'.uniqid()],
        );
    }
}
