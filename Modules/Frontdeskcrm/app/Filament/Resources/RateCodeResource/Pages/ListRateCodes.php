<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RateCodeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Frontdeskcrm\Filament\Resources\RateCodeResource;

class ListRateCodes extends ListRecords
{
    protected static string $resource = RateCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
