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
