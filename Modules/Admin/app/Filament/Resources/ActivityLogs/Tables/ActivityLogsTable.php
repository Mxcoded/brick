<?php

namespace Modules\Admin\Filament\Resources\ActivityLogs\Tables;

use App\Models\UserActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\ActivityLogs\ActivityLogResource;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->date('M d, H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->formatStateUsing(fn (UserActivityLog $record, ?string $state): string => $state ?? 'System')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => ActivityLogResource::verbColor($state))
                    ->formatStateUsing(fn (string $state): string => ActivityLogResource::verbLabel($state))
                    ->description(fn (string $state): ?string => ActivityLogResource::resourceLabel($state) ?: null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('method')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options(fn (): array => UserActivityLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all()),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // Read-only log: actions are intentionally omitted.
            ]);
    }
}
