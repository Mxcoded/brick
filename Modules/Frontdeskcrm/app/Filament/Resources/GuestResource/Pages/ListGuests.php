<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Frontdeskcrm\Filament\Pages\ImportGuests;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Widgets\GuestStatsOverview;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            GuestStatsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import guests')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                // The Filament page, not the legacy Blade form.
                ->url(ImportGuests::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
