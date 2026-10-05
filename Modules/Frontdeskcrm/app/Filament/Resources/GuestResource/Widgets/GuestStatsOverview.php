<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;

/**
 * The four figures the legacy guest directory showed above its table:
 * total guests, arrivals in the last 30 days, guests with more than one
 * completed stay, and profiles created this month.
 *
 * Returning guests are counted on `visit_count`, matching the legacy tile, so
 * the two screens cannot disagree about the same word.
 */
class GuestStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Guest directory';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total guests', Guest::count())
                ->description('Profiles on file')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Recent arrivals', Guest::recentVisitors(30)->count())
                ->description('Checked out in the last 30 days')
                ->descriptionIcon('heroicon-m-calendar-days'),

            Stat::make('Returning guests', Guest::where('visit_count', '>', 1)->count())
                ->description('More than one completed stay')
                ->descriptionIcon('heroicon-m-arrow-path'),

            Stat::make('New this month', Guest::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count())
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-sparkles'),
        ];
    }

    public static function canView(): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::GUESTS_READ,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }
}
