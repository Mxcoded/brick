<?php

namespace Modules\Admin\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary')
                ->extraAttributes(['style' => $this->statStyle('primary')]),

            Stat::make('Active', User::active()->count())
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->extraAttributes(['style' => $this->statStyle('success')]),

            Stat::make('Staff Accounts', User::staff()->count())
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('info')
                ->extraAttributes(['style' => $this->statStyle('info')]),

            Stat::make('Guest Accounts', User::guest()->count())
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('gray')
                ->extraAttributes(['style' => $this->statStyle('gray')]),

            Stat::make('Suspended', User::suspended()->count())
                ->icon(Heroicon::OutlinedPauseCircle)
                ->color('warning')
                ->extraAttributes(['style' => $this->statStyle('warning')]),

            Stat::make('Deactivated', User::deactivated()->count())
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->extraAttributes(['style' => $this->statStyle('danger')]),

            Stat::make('New This Month', User::whereBetween('created_at', [now()->startOfMonth(), now()])->count())
                ->icon(Heroicon::OutlinedCalendarDays)
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
