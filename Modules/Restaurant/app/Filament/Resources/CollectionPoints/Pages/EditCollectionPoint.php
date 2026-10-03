<?php

namespace Modules\Restaurant\Filament\Resources\CollectionPoints\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Restaurant\Filament\Resources\CollectionPoints\CollectionPointResource;

class EditCollectionPoint extends EditRecord
{
    protected static string $resource = CollectionPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
