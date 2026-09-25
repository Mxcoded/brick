<?php

namespace Modules\Admin\Filament\Resources\LoginLogs\Pages;

use App\Models\UserLoginLog;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\LoginLogs\LoginLogResource;
use Modules\Admin\Filament\Resources\LoginLogs\Widgets\LoginLogStatsOverview;

class ActiveSessions extends ListRecords
{
    protected static string $resource = LoginLogResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected function getHeaderWidgets(): array
    {
        return [
            LoginLogStatsOverview::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        return UserLoginLog::query()
            ->where('status', 'success')
            ->whereNull('logged_out_at')
            ->orderByDesc('last_activity_at');
    }
}
