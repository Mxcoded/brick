<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\RevenueBySourceExport;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

/**
 * Revenue and cost by operating area.
 *
 * A hotel's departments are judged on contribution, not on revenue alone, so
 * this pairs each area's revenue against the cost posted against it.
 */
class RevenueBySource extends FinanceReportPage
{
    protected static ?string $title = 'Revenue by Source';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Revenue by Source';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'finance/revenue-by-source';

    protected string $view = 'finance::filament.pages.revenue-by-source';

    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::revenueBySource($from, $to);
    }

    protected function exportClass(): ?string
    {
        return RevenueBySourceExport::class;
    }
}
