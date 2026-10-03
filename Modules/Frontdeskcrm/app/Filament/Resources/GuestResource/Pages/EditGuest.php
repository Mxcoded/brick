<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
