<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Widgets\ChartOfAccountStatsOverview;

class ListChartOfAccounts extends ListRecords
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ChartOfAccountStatsOverview::class,
        ];
    }
}
