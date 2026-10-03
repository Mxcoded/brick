<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Accounts', ChartOfAccount::count())
                ->icon(Heroicon::OutlinedListBullet)
                ->color('primary')
                ->extraAttributes(['style' => $this->statStyle('primary')]),

            Stat::make('Active', ChartOfAccount::active()->count())
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->extraAttributes(['style' => $this->statStyle('success')]),

            Stat::make('Inactive', ChartOfAccount::where('active', false)->count())
                ->icon(Heroicon::OutlinedPauseCircle)
                ->color('warning')
                ->extraAttributes(['style' => $this->statStyle('warning')]),

            Stat::make('Contra Accounts', ChartOfAccount::where('is_contra', true)->count())
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->color('gray')
                ->extraAttributes(['style' => $this->statStyle('gray')]),
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
