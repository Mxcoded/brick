<?php

namespace Modules\Website\Filament\Resources\RoomTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Website\Filament\Resources\RoomTypeResource;

class EditRoomType extends EditRecord
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
