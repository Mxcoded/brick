<?php

namespace Modules\Admin\Filament\Resources\Permissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Permissions\PermissionResource;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
