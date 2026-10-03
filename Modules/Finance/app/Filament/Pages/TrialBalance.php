<?php

namespace Modules\Finance\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Exports\TrialBalanceExport;
use Modules\Finance\Support\LedgerReport;
use UnitEnum;

class TrialBalance extends FinanceReportPage
{
    protected static ?string $title = 'Trial Balance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Trial Balance';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'finance/trial-balance';

    protected string $view = 'finance::filament.pages.trial-balance';

    protected function reportData(?string $from, ?string $to): array
    {
        return LedgerReport::trialBalance($from, $to);
    }

    protected function exportClass(): ?string
    {
        return TrialBalanceExport::class;
    }
}
