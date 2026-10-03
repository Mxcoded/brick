<?php

namespace Modules\Restaurant\Filament\Resources\CollectionPoints\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Restaurant\Filament\Resources\CollectionPoints\CollectionPointResource;

class ListCollectionPoints extends ListRecords
{
    protected static string $resource = CollectionPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
