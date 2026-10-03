<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Frontdeskcrm\Filament\Resources\GuestTypeResource;

class EditGuestType extends EditRecord
{
    protected static string $resource = GuestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
