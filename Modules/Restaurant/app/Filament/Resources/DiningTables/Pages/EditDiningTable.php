<?php

namespace Modules\Restaurant\Filament\Resources\DiningTables\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Restaurant\Filament\Resources\DiningTables\DiningTableResource;

class EditDiningTable extends EditRecord
{
    protected static string $resource = DiningTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
