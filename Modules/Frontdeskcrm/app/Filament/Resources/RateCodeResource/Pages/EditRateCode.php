<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RateCodeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Frontdeskcrm\Filament\Resources\RateCodeResource;

class EditRateCode extends EditRecord
{
    protected static string $resource = RateCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
