<?php

namespace Modules\Admin\Filament\Resources\PaymentGateways\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Website\Models\PaymentGateway;

class PaymentGatewaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PaymentGateway $record): ?string => $record->driver),

                TextColumn::make('code')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),

                TextColumn::make('is_default')
                    ->label('Default')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'primary' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Default' : '—'),
            ])
            ->recordActions([
                Action::make('set-default')
                    ->label('Set Default')
                    ->icon(Heroicon::OutlinedStar)
                    ->visible(fn (?PaymentGateway $record): bool => $record?->is_default !== true)
                    ->action(function (PaymentGateway $record): void {
                        PaymentGateway::where('id', '!=', $record->id)->update(['is_default' => false]);
                        $record->update(['is_default' => true, 'is_active' => true]);
                    }),

                Action::make('toggle-active')
                    ->icon(Heroicon::OutlinedPower)
                    ->color(fn (PaymentGateway $record): string => $record->is_active ? 'warning' : 'success')
                    ->label(fn (PaymentGateway $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->action(function (PaymentGateway $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
