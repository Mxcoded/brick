<?php

namespace Modules\Admin\Filament\Resources\LoginLogs\Widgets;

use App\Models\UserLoginLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoginLogStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Logins Today', UserLoginLog::today()->successful()->count())
                ->color('primary'),
            Stat::make('Unique Users Today', UserLoginLog::today()->successful()->distinct('user_id')->count('user_id'))
                ->color('info'),
            Stat::make('Active Now', UserLoginLog::active()->count())
                ->color('success'),
            Stat::make('Idle Sessions', UserLoginLog::stale()->count())
                ->color('warning'),
            Stat::make('Failed Today', UserLoginLog::today()->failed()->count())
                ->color('danger'),
            Stat::make('This Week', UserLoginLog::whereBetween('logged_in_at', [now()->startOfWeek(), now()])->successful()->count())
                ->color('gray'),
            Stat::make('This Month', UserLoginLog::whereBetween('logged_in_at', [now()->startOfMonth(), now()])->successful()->count())
                ->color('dark'),
        ];
    }
}
