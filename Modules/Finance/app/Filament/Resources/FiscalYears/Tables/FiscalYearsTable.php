<?php

namespace Modules\Finance\Filament\Resources\FiscalYears\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Support\FinancePermission;

class FiscalYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('periods_count')
                    ->counts('periods')
                    ->label('Periods')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_closed')
                    ->label('Closed')
                    ->boolean(),
            ])
            ->defaultSort('start_date', 'desc')
            ->actions([
                Action::make('generatePeriods')
                    ->label('Generate periods')
                    ->icon('heroicon-o-calendar-days')
                    ->requiresConfirmation()
                    ->modalHeading('Generate monthly periods')
                    ->modalDescription('Creates the monthly periods for this year, clipped to its start and end dates. Existing periods are left untouched.')
                    ->color('primary')
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::FISCAL_YEARS_UPDATE))
                    ->action(fn (FiscalYear $record) => $record->generateMonthlyPeriods()),

                Action::make('close')
                    ->label('Close year')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Close this fiscal year?')
                    ->modalDescription('Every period in this year will be locked and no further entries can be posted to these dates.')
                    ->visible(fn (FiscalYear $record): bool => ! $record->is_closed
                        && (FinancePermission::allows(FinancePermission::FISCAL_YEARS_UPDATE)))
                    ->action(fn (FiscalYear $record) => $record->close()),

                EditAction::make()
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::FISCAL_YEARS_UPDATE)),
            ]);
    }
}
