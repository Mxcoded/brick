<?php

namespace Modules\Website\Filament\Resources\RoomTypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Website\Filament\Resources\RoomTypeResource;

class ViewRoomType extends ViewRecord
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The units table and the image gallery are still Blade screens. Both
            // are linked from here so the type and its physical rooms stay one
            // click apart instead of on two different admin surfaces.
            Actions\Action::make('units')
                ->label('Manage units')
                ->icon('heroicon-o-building-office-2')
                ->color('gray')
                ->url(fn (): string => route('website.admin.room-types.show', $this->record)),
            Actions\Action::make('calendar')
                ->label('Room calendar')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->url(fn (): string => route('website.admin.rooms.calendar')),
            Actions\EditAction::make(),
        ];
    }
}
