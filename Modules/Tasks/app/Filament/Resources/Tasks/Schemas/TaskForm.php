<?php

namespace Modules\Tasks\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Support\TaskPermission;

class TaskForm
{
    public const PRIORITIES = ['high', 'medium', 'low'];

    public const STATUSES = ['pending', 'in_progress', 'completed'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // The reference is allocated by TaskService inside the create
                // transaction, so it is not a form field. Setting it here would
                // burn a number on every page render.

                Textarea::make('description')
                    ->required()
                    ->maxLength(5000)
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('priority')
                    ->required()
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->default('medium'),

                DatePicker::make('deadline')
                    ->required()
                    ->label('Deadline')
                    ->minDate(now()->toDateString()),

                Select::make('assignees')
                    ->label('Assigned to')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(fn (): array => static::employeeOptions())
                    ->visible(fn (): bool => TaskPermission::allows(TaskPermission::ASSIGN))
                    ->helperText('Leave empty to assign to yourself.')
                    ->columnSpanFull(),

                Toggle::make('is_recurring')
                    ->label('Repeats')
                    ->live()
                    ->columnSpanFull(),

                Select::make('recurrence_type')
                    ->label('Repeat every')
                    ->options([
                        'daily' => 'Day',
                        'weekly' => 'Week',
                        'biweekly' => '2 weeks',
                        'monthly' => 'Month',
                    ])
                    ->required(fn (Get $get): bool => (bool) $get('is_recurring'))
                    ->visible(fn (Get $get): bool => (bool) $get('is_recurring')),

                DatePicker::make('recurrence_end_date')
                    ->label('Repeat until')
                    ->minDate(fn (Get $get) => $get('deadline'))
                    // Left optional so a task can repeat indefinitely, which is
                    // what the service and the old form allowed.
                    ->visible(fn (Get $get): bool => (bool) $get('is_recurring'))
                    ->helperText('Optional. Leave empty to repeat until you stop it.'),
            ]);
    }

    /**
     * Only active staff can be given a task; the old pages filtered on
     * end_date for the same reason.
     *
     * @return array<int, string>
     */
    protected static function employeeOptions(): array
    {
        if (! class_exists(Employee::class)) {
            return [];
        }

        return Employee::whereNull('end_date')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
