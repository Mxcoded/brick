<?php

namespace Modules\Restaurant\Filament\Resources\MenuItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Restaurant\Filament\Resources\MenuItems\MenuItemResource;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;
}
