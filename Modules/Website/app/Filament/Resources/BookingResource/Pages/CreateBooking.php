<?php

namespace Modules\Website\Filament\Resources\BookingResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Website\Filament\Resources\BookingResource;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
}
