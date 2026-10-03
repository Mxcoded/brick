<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Support\TaskPermission;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('deadline')
            ->columns([
                TextColumn::make('task_number')
                    ->label('Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('description')
                    ->label('Task')
                    ->searchable()
                    ->wrap()
                    ->limit(60),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('employees.name')
                    ->label('Assigned to')
                    ->badge()
                    ->separator(',')
                    ->placeholder('Unassigned')
                    ->toggleable(),

                TextColumn::make('deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->description(fn (Task $record): ?string => $record->isOverdue() ? 'Overdue' : null),

                IconColumn::make('is_recurring')
                    ->label('Repeats')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Raised by')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Raised')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In progress',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),

                SelectFilter::make('priority')
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->multiple(),

                SelectFilter::make('assignee_id')
                    ->label('Assigned to')
                    ->relationship('employees', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_recurring')
                    ->label('Repeating only')
                    ->trueLabel('Repeating')
                    ->falseLabel('One-off'),

                Filter::make('overdue')
                    ->label('Overdue only')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', '!=', 'completed')
                        ->whereDate('deadline', '<', Carbon::today())),

                Filter::make('created_by_me')
                    ->label('Raised by me')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('created_by', auth()->id())),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_in_progress')
                        ->label('Mark in progress')
                        ->icon('heroicon-o-play')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => TaskPermission::allows(TaskPermission::UPDATE))
                        ->action(function (Collection $records): void {
                            $service = app(TaskService::class);

                            foreach ($records as $task) {
                                $service->bulkChangeStatus(auth()->user(), [$task->id], 'in_progress');
                            }
                        }),

                    BulkAction::make('mark_completed')
                        ->label('Mark completed')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => TaskPermission::allows(TaskPermission::UPDATE))
                        ->action(function (Collection $records): void {
                            $service = app(TaskService::class);

                            foreach ($records as $task) {
                                $service->bulkChangeStatus(auth()->user(), [$task->id], 'completed');
                            }
                        }),

                    BulkAction::make('mark_pending')
                        ->label('Reset to pending')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => TaskPermission::allows(TaskPermission::UPDATE))
                        ->action(function (Collection $records): void {
                            $service = app(TaskService::class);

                            foreach ($records as $task) {
                                $service->bulkChangeStatus(auth()->user(), [$task->id], 'pending');
                            }
                        }),

                    BulkAction::make('assign')
                        ->label('Assign to')
                        ->icon('heroicon-o-user-plus')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => TaskPermission::allows(TaskPermission::ASSIGN))
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Select::make('employee_id')
                                ->label('Employee')
                                ->required()
                                ->searchable()
                                ->options(fn (): array => Employee::whereNull('end_date')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $service = app(TaskService::class);

                            $service->bulkAssign(
                                auth()->user(),
                                $records->modelKeys(),
                                (int) $data['employee_id'],
                            );
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
