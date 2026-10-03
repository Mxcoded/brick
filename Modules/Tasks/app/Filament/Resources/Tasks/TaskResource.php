<?php

namespace Modules\Tasks\Filament\Resources\Tasks;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Tasks\Filament\Pages\TaskKanban;
use Modules\Tasks\Filament\Resources\Tasks\Pages\CreateTask;
use Modules\Tasks\Filament\Resources\Tasks\Pages\EditTask;
use Modules\Tasks\Filament\Resources\Tasks\Pages\ListTasks;
use Modules\Tasks\Filament\Resources\Tasks\Pages\ViewTask;
use Modules\Tasks\Filament\Resources\Tasks\RelationManagers\CommentsRelationManager;
use Modules\Tasks\Filament\Resources\Tasks\RelationManagers\UpdatesRelationManager;
use Modules\Tasks\Filament\Resources\Tasks\Schemas\TaskForm;
use Modules\Tasks\Filament\Resources\Tasks\Tables\TasksTable;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Support\TaskPermission;
use UnitEnum;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'task';

    protected static ?string $pluralModelLabel = 'tasks';

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $slug = 'tasks';

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return TaskPermission::allowsAny(
            TaskPermission::DASHBOARD_ACCESS,
            TaskPermission::READ,
        );
    }

    /**
     * Staff only see tasks they raised or were assigned, which is the same rule
     * the original list page used.
     */
    public static function canView($record): bool
    {
        if (TaskPermission::allows(TaskPermission::READ) && $record->created_by === auth()->id()) {
            return true;
        }

        return app(TaskService::class)->canActOn($record, auth()->user());
    }

    public static function canCreate(): bool
    {
        return TaskPermission::allows(TaskPermission::CREATE);
    }

    /**
     * Editing a task's content, its recurrence or its assignees is the creator's
     * job, matching the old edit page.
     */
    public static function canEdit($record): bool
    {
        return TaskPermission::allows(TaskPermission::UPDATE)
            && $record->created_by === auth()->id();
    }

    public static function canDelete($record): bool
    {
        return TaskPermission::allows(TaskPermission::DELETE)
            && $record->created_by === auth()->id();
    }

    public static function getEloquentQuery(): Builder
    {
        return app(TaskService::class)->visibleTo(auth()->user());
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            UpdatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'view' => ViewTask::route('/{record}'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }

    /**
     * The task board lives in the panel now. The old Blade board is still
     * reachable at tasks.index?view=kanban for anyone holding a bookmark.
     */
    public static function kanbanUrl(): ?string
    {
        return TaskKanban::canAccess() ? TaskKanban::getUrl() : null;
    }
}
