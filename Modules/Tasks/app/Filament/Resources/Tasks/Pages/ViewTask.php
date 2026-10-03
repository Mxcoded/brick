<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Task')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('task_number')
                            ->label('Ref')
                            ->badge()
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state)))
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'in_progress' => 'info',
                                default => 'warning',
                            }),

                        TextEntry::make('description')
                            ->columnSpanFull(),

                        TextEntry::make('priority')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->color(fn (string $state): string => match ($state) {
                                'high' => 'danger',
                                'medium' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('deadline')
                            ->date('d M Y')
                            ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null),

                        TextEntry::make('employees.name')
                            ->label('Assigned to')
                            ->badge(),

                        TextEntry::make('creator.name')
                            ->label('Raised by'),

                        TextEntry::make('created_at')
                            ->label('Raised')
                            ->since(),

                        TextEntry::make('completion_date')
                            ->label('Completed')
                            ->date('d M Y')
                            ->placeholder('Not completed'),

                        TextEntry::make('recurrence_type')
                            ->label('Repeats')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? str_replace('biweekly', '2 weeks', $state) : null)
                            ->placeholder('One-off task'),

                        TextEntry::make('recurrence_end_date')
                            ->label('Repeat until')
                            ->date('d M Y')
                            ->placeholder('No end date'),
                    ]),

                Section::make('Details')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),

                        TextEntry::make('non_completion_reason')
                            ->label('Reason not completed')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Status is changed from here rather than from the edit form, so that
     * completing a task always runs the completion-date and recurrence logic.
     */
    protected function getHeaderActions(): array
    {
        $task = $this->getRecord();
        $service = app(TaskService::class);
        $canAct = $service->canActOn($task, auth()->user());

        return [
            Action::make('start')
                ->label('Start')
                ->icon('heroicon-o-play')
                ->color('info')
                ->visible(fn (): bool => $canAct && $task->status === 'pending')
                ->action(fn () => $this->changeStatus('in_progress')),

            Action::make('complete')
                ->label('Mark complete')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $canAct && $task->status !== 'completed')
                ->requiresConfirmation()
                // A repeating task silently creates its next occurrence, so say so.
                ->modalDescription(fn (): ?string => $task->is_recurring
                    ? 'This task repeats, so completing it will create the next occurrence.'
                    : null)
                ->action(fn () => $this->changeStatus('completed')),

            Action::make('reopen')
                ->label('Reopen')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->visible(fn (): bool => $canAct && $task->status === 'completed')
                ->action(fn () => $this->changeStatus('pending')),

            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => $this->getResource()::getUrl('edit', ['record' => $task])),

            Action::make('kanban')
                ->label('Kanban board')
                ->icon('heroicon-o-view-columns')
                ->url(fn (): ?string => TaskResource::kanbanUrl())
                ->visible(fn (): bool => TaskResource::kanbanUrl() !== null),
        ];
    }

    protected function changeStatus(string $status): void
    {
        app(TaskService::class)->changeStatus($this->getRecord(), auth()->user(), $status);

        // Refresh first, so the infolist re-reads the new status and any
        // recurrence the service created.
        $this->getRecord()->refresh();

        $this->fillForm();
    }
}
