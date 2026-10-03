<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;

class CreateChartOfAccount extends CreateRecord
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
