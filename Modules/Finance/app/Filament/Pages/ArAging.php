<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\ArAgingExport;
use Modules\Finance\Exports\FinanceReportExport;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

/**
 * Who owes the hotel money, and how late they are.
 *
 * Credit control lives in Finance rather than in the front desk because the
 * figure is a receivable on the balance sheet: it has to agree with the ledger,
 * and it is what the hotel's own auditors ask for. The underlying charges are
 * the city ledger folios and group bookings the front desk raises, but nothing
 * here depends on that module.
 */
class ArAging extends FinanceReportPage
{
    protected static ?string $title = 'A/R Aging';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'A/R Aging';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'finance/ar-aging';

    protected string $view = 'finance::filament.pages.ar-aging';

    /**
     * An aging is always "as at" a single date, so the inherited from/to period
     * does not apply: the report ages the whole ledger to one date instead.
     *
     * @return array<string, mixed>
     */
    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::receivableAging();
    }

    protected function exportClass(): ?string
    {
        return ArAgingExport::class;
    }

    protected function newExport(): ?FinanceReportExport
    {
        return new ArAgingExport(LedgerReport::receivableAging()['asOf']);
    }
}
