<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Tasks\Services\TaskService;

class TaskStatsOverview extends StatsOverviewWidget
{
    /**
     * @return array<string, mixed>
     */
    protected function getStats(): array
    {
        $stats = app(TaskService::class)->statsFor(auth()->user());

        return [
            Stat::make('My Tasks', $stats['total'])
                ->description('Raised by you or assigned to you')
                ->color('primary')
                ->extraAttributes(['style' => $this->statStyle('primary')]),

            Stat::make('Pending', $stats['pending'])
                ->color('warning')
                ->extraAttributes(['style' => $this->statStyle('warning')]),

            Stat::make('In Progress', $stats['in_progress'])
                ->color('info')
                ->extraAttributes(['style' => $this->statStyle('info')]),

            Stat::make('Completed', $stats['completed'])
                ->color('success')
                ->extraAttributes(['style' => $this->statStyle('success')]),

            Stat::make('Overdue', $stats['overdue'])
                ->description('Past the deadline and still open')
                ->color($stats['overdue'] > 0 ? 'danger' : 'gray')
                ->extraAttributes(['style' => $this->statStyle($stats['overdue'] > 0 ? 'danger' : 'gray')]),
        ];
    }

    /**
     * Tints each card with its own accent, matching the Finance stats widgets.
     */
    protected function statStyle(string $color): string
    {
        $variable = $color === 'dark' ? '--gray-900' : "--{$color}-500";

        return "background-color: oklch(from var({$variable}) l c h / 0.09); --tw-ring-color: oklch(from var({$variable}) l c h / 0.25);";
    }
}
