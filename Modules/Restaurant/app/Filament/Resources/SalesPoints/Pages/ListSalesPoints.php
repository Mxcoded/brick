<?php

namespace Modules\Restaurant\Filament\Resources\SalesPoints\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Restaurant\Filament\Resources\SalesPoints\SalesPointResource;

class ListSalesPoints extends ListRecords
{
    protected static string $resource = SalesPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
