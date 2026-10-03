<?php

namespace Modules\Restaurant\Filament\Resources\SalesPoints\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Restaurant\Filament\Resources\SalesPoints\SalesPointResource;

class EditSalesPoint extends EditRecord
{
    protected static string $resource = SalesPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
