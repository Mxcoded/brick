<?php

namespace Modules\Admin\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\PermissionCatalog;
use Spatie\Permission\Models\Permission;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('module')
                    ->label('Module')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state, Permission $record) => PermissionCatalog::groupLabel(PermissionCatalog::groupFor($record->name))),

                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->badge()
                    ->label('Roles')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Module')
                    ->options(PermissionCatalog::groupOptions())
                    ->query(function (Builder $query, array $state): Builder {
                        $value = $state['value'] ?? null;

                        return $value
                            ? $query->whereIn('id', PermissionCatalog::idsForGroup($value))
                            : $query;
                    }),

                SelectFilter::make('guard_name')
                    ->options([
                        'web' => 'web',
                    ]),

                TernaryFilter::make('roles')
                    ->label('Has roles assigned')
                    ->queries(
                        true: fn ($query) => $query->whereHas('roles'),
                        false: fn ($query) => $query->whereDoesntHave('roles'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
