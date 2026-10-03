<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Guests')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->url(route('frontdesk.guests.import')),
            Actions\CreateAction::make(),
        ];
    }
}
