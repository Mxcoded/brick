<?php

namespace Modules\Restaurant\Filament\Resources\MenuCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Restaurant\Filament\Resources\MenuCategories\MenuCategoryResource;

class CreateMenuCategory extends CreateRecord
{
    protected static string $resource = MenuCategoryResource::class;
}
