<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Roles\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
