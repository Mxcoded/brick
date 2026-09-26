<?php

namespace Modules\Admin\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->permissions_count > 0
                        ? 'Has '.$record->permissions_count.' permission(s)'
                        : 'No permissions yet')
                    ->grow(),

                TextColumn::make('guard_name')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->badge()
                    ->label('Assigned users')
                    ->icon(Heroicon::OutlinedUsers)
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->badge()
                    ->label('Permissions')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                SelectFilter::make('guard_name')
                    ->options(fn (): array => Role::query()
                        ->distinct()
                        ->pluck('guard_name', 'guard_name')
                        ->toArray()),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
