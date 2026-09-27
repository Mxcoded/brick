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
                ->color('primary')
                ->extraAttributes(['style' => $this->statStyle('primary')]),
            Stat::make('Unique Users Today', UserLoginLog::today()->successful()->distinct('user_id')->count('user_id'))
                ->color('info')
                ->extraAttributes(['style' => $this->statStyle('info')]),
            Stat::make('Active Now', UserLoginLog::active()->count())
                ->color('success')
                ->extraAttributes(['style' => $this->statStyle('success')]),
            Stat::make('Idle Sessions', UserLoginLog::stale()->count())
                ->color('warning')
                ->extraAttributes(['style' => $this->statStyle('warning')]),
            Stat::make('Failed Today', UserLoginLog::today()->failed()->count())
                ->color('danger')
                ->extraAttributes(['style' => $this->statStyle('danger')]),
            Stat::make('This Week', UserLoginLog::whereBetween('logged_in_at', [now()->startOfWeek(), now()])->successful()->count())
                ->color('gray')
                ->extraAttributes(['style' => $this->statStyle('gray')]),
            Stat::make('This Month', UserLoginLog::whereBetween('logged_in_at', [now()->startOfMonth(), now()])->successful()->count())
                ->color('gray')
                ->extraAttributes(['style' => $this->statStyle('dark')]),
        ];
    }

    /**
     * Returns a theme-relative, translucent background for the stat card so its
     * card surface is tinted with the stat's own accent color. The value and
     * label stay fully readable since the fill is a low-alpha wash of the
     * Filament color CSS variable.
     */
    protected function statStyle(string $color): string
    {
        $variable = $color === 'dark' ? '--gray-900' : "--{$color}-500";

        return "background-color: oklch(from var({$variable}) l c h / 0.09); --tw-ring-color: oklch(from var({$variable}) l c h / 0.25);";
    }
}
