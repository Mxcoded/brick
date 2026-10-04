<?php

namespace Modules\Website\Filament\Resources\BookingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\BookingResource;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Guarded by canCreate(), so a role without website.bookings.create gets
            // the list and not the button.
            Actions\CreateAction::make(),
        ];
    }
}
