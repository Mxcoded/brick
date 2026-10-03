<?php

namespace Modules\Finance\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Finance\Exceptions\FiscalPeriodLockedException;
use Modules\Finance\Models\FiscalPeriod;
use Modules\Finance\Models\FiscalYear;

class FiscalPeriodService
{
    /**
     * The period covering the given date, or null when no fiscal calendar has
     * been configured (in which case posting stays unrestricted).
     */
    public function periodFor(Carbon|string $date): ?FiscalPeriod
    {
        return FiscalPeriod::forDate($date)
            ->orderBy('start_date')
            ->first();
    }

    public function isLockedFor(Carbon|string $date): bool
    {
        return $this->periodFor($date)?->isLocked() ?? false;
    }

    public function assertOpenFor(Carbon|string $date): void
    {
        $period = $this->periodFor($date);

        if ($period?->isLocked()) {
            throw FiscalPeriodLockedException::forPeriod($period);
        }
    }

    public function currentPeriod(): ?FiscalPeriod
    {
        return $this->periodFor(now());
    }

    /**
     * Configured periods, newest first, for filter dropdowns.
     *
     * @return Collection<int, FiscalPeriod>
     */
    public function periodsForFilter()
    {
        return FiscalPeriod::with('fiscalYear')
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * Default report window: the period containing today, else the active
     * fiscal year, else null to keep the unfiltered behaviour.
     *
     * @return array{from: string, to: string}|null
     */
    public function defaultReportRange(): ?array
    {
        $period = $this->currentPeriod();

        if ($period) {
            return [
                'from' => $period->start_date->toDateString(),
                'to' => $period->end_date->toDateString(),
            ];
        }

        $year = FiscalYear::current();

        if ($year) {
            return [
                'from' => $year->start_date->toDateString(),
                'to' => $year->end_date->toDateString(),
            ];
        }

        return null;
    }
}
