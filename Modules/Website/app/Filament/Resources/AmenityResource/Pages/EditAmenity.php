<?php

namespace Modules\Website\Filament\Resources\AmenityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Website\Filament\Resources\AmenityResource;

class EditAmenity extends EditRecord
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
