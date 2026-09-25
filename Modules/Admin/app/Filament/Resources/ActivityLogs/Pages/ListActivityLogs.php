<?php

namespace Modules\Admin\Filament\Resources\ActivityLogs\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\ActivityLogs\ActivityLogResource;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;
}
