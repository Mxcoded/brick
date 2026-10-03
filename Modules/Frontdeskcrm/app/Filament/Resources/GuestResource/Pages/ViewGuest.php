<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
