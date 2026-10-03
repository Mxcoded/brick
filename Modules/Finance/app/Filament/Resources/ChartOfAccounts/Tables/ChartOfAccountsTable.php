<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\ChartOfAccountsExport;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Support\FinancePermission;

class ChartOfAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export to Excel')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_EXPORT))
                    ->action(fn (): mixed => Excel::download(
                        new ChartOfAccountsExport,
                        'chart-of-accounts-'.now()->format('Y-m-d').'.xlsx'
                    )),
            ])
            ->columns([
                TextColumn::make('code')
                    ->label('GL Code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'asset' => 'info',
                        'liability' => 'warning',
                        'equity' => 'success',
                        'income' => 'primary',
                        'expense' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('normal_balance')
                    ->label('Normal Bal.')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'debit' ? 'gray' : 'info'),

                IconColumn::make('is_contra')
                    ->label('Contra')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                TextColumn::make('parent.code')
                    ->label('Parent')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),

                TernaryFilter::make('active')
                    ->label('Active only'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete account')
                    ->modalDescription('Deleting an account removes it from the chart of accounts. This cannot be undone.')
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_DELETE))
                    ->authorize(fn (): bool => FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_DELETE))
                    ->action(function (ChartOfAccount $record): void {
                        // Defence in depth: re-check the permission at execution time.
                        if (! FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_DELETE)) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete this account')
                                ->body('You do not have permission to manage the chart of accounts.')
                                ->send();

                            return;
                        }

                        if ($record->children()->exists() || $record->lines()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete this account')
                                ->body('It has child accounts or posted transactions. Deactivate it instead.')
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Account deleted.')
                            ->send();
                    }),
            ])
            ->defaultSort('code');
    }
}
