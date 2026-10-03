<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
