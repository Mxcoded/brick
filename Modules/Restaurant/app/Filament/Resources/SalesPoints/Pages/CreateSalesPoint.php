<?php

namespace Modules\Restaurant\Filament\Resources\SalesPoints\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Restaurant\Filament\Resources\SalesPoints\SalesPointResource;

class CreateSalesPoint extends CreateRecord
{
    protected static string $resource = SalesPointResource::class;
}
