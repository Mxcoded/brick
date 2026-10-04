<?php

namespace Modules\Website\Filament\Resources\AddonResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Website\Filament\Resources\AddonResource;

class EditAddon extends EditRecord
{
    protected static string $resource = AddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
