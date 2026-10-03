<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;

class ViewRegistration extends ViewRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
