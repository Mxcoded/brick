<?php

namespace Modules\Finance\Filament\Resources\FiscalYears\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Support\FinancePermission;

class FiscalPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'periods';

    protected static ?string $title = 'Accounting periods';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'locked' ? 'danger' : 'success'),

                TextColumn::make('locked_at')
                    ->label('Locked at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('start_date')
            ->headerActions([
                Action::make('generatePeriods')
                    ->label('Generate monthly periods')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Creates any missing monthly periods for this year. Existing periods are left untouched.')
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::FISCAL_YEARS_UPDATE))
                    ->action(function (): void {
                        /** @var FiscalYear $record */
                        $record = $this->getOwnerRecord();
                        $record->generateMonthlyPeriods();
                    }),
            ])
            ->actions([
                Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('No further journal entries can be posted to dates inside this period.')
                    ->visible(fn ($record): bool => $record->status === 'open'
                        && (FinancePermission::allows(FinancePermission::PERIODS_UPDATE)))
                    ->action(fn ($record) => $record->lock()),

                Action::make('unlock')
                    ->label('Unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Posting will be re-enabled for this period.')
                    ->visible(fn ($record): bool => $record->status === 'locked'
                        && (FinancePermission::allows(FinancePermission::PERIODS_UPDATE)))
                    ->action(fn ($record) => $record->unlock()),
            ]);
    }
}
