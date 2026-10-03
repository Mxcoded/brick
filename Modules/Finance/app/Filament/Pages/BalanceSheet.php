<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\BalanceSheetExport;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

class BalanceSheet extends FinanceReportPage
{
    protected static ?string $title = 'Balance Sheet';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Balance Sheet';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'finance/balance-sheet';

    protected string $view = 'finance::filament.pages.balance-sheet';

    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::balanceSheet($from, $to);
    }

    protected function exportClass(): ?string
    {
        return BalanceSheetExport::class;
    }
}
