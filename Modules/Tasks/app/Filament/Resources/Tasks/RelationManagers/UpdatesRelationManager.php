<?php

namespace Modules\Tasks\Filament\Resources\Tasks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The read-only history of everything that has happened to a task.
 */
class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    protected static ?string $title = 'Activity';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('By')
                    ->default('System')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'created_from_recurring' => 'success',
                        'comment_added' => 'info',
                        'status_changed', 'bulk_status_changed' => 'warning',
                        'assignees_changed', 'assignee_added' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('changes')
                    ->label('Details')
                    ->formatStateUsing(fn (?array $state): string => $this->describe($state))
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }

    /**
     * Render the stored change array as "field: from -> to" lines. The activity
     * column casts to array, so anything unreadable falls back to the raw value
     * rather than blowing up the page.
     *
     * @param  array<int|string, mixed>|null  $changes
     */
    protected function describe(?array $changes): string
    {
        if (! $changes) {
            return '';
        }

        $lines = [];

        foreach ($changes as $field => $value) {
            if (is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                $from = $this->stringify($value['from']);
                $to = $this->stringify($value['to']);

                $lines[] = "{$field}: {$from} -> {$to}";

                continue;
            }

            $lines[] = "{$field}: ".$this->stringify($value);
        }

        return implode(', ', $lines);
    }

    protected function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map(fn ($item): string => (string) $item, $value));
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        return $value === null || $value === '' ? '—' : (string) $value;
    }
}
