<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Resources\JournalEntries\Widgets\JournalEntryStatsOverview;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JournalEntryStatsOverview::class,
        ];
    }
}
