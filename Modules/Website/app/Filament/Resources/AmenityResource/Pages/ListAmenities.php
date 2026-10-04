<?php

namespace Modules\Website\Filament\Resources\AmenityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\AmenityResource;

class ListAmenities extends ListRecords
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
