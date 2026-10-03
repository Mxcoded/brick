<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Frontdeskcrm\Filament\Resources\GuestTypeResource;

class ListGuestTypes extends ListRecords
{
    protected static string $resource = GuestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
