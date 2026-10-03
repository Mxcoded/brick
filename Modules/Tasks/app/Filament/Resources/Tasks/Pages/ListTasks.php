<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Filament\Resources\Tasks\Widgets\TaskStatsOverview;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The kanban board has no Filament equivalent yet, so the list page
            // offers it rather than leaving the feature unreachable.
            Action::make('kanban')
                ->label('Kanban board')
                ->icon('heroicon-o-view-columns')
                ->url(fn (): ?string => TaskResource::kanbanUrl())
                ->visible(fn (): bool => TaskResource::kanbanUrl() !== null),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TaskStatsOverview::class,
        ];
    }
}
