<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;

class EditChartOfAccount extends EditRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
