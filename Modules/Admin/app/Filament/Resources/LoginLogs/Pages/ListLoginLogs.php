<?php

namespace Modules\Admin\Filament\Resources\LoginLogs\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\LoginLogs\LoginLogResource;
use Modules\Admin\Filament\Resources\LoginLogs\Widgets\LoginLogStatsOverview;

class ListLoginLogs extends ListRecords
{
    protected static string $resource = LoginLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            LoginLogStatsOverview::class,
        ];
    }
}
