<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\JournalEntry;

class JournalEntryStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Entries', JournalEntry::count())
                ->icon(Heroicon::OutlinedBookOpen)
                ->color('primary')
                ->extraAttributes(['style' => $this->statStyle('primary')]),

            Stat::make('Posted', JournalEntry::where('status', 'posted')->count())
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->extraAttributes(['style' => $this->statStyle('success')]),

            Stat::make('Draft', JournalEntry::where('status', 'draft')->count())
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('warning')
                ->extraAttributes(['style' => $this->statStyle('warning')]),

            Stat::make('Posted This Month', JournalEntry::where('status', 'posted')->whereBetween('date', [now()->startOfMonth(), now()])->count())
                ->icon(Heroicon::OutlinedCalendarDays)
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
