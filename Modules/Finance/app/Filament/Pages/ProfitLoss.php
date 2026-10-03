<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\ProfitLossExport;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

class ProfitLoss extends FinanceReportPage
{
    protected static ?string $title = 'Profit & Loss';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Profit & Loss';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'finance/profit-loss';

    protected string $view = 'finance::filament.pages.profit-loss';

    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::profitLoss($from, $to);
    }

    protected function exportClass(): ?string
    {
        return ProfitLossExport::class;
    }
}
