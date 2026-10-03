<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\JournalEntriesExport;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Support\FinancePermission;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export to Excel')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (): bool => FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_EXPORT))
                    // Export the filtered query so the file matches the screen.
                    ->action(fn (): mixed => Excel::download(
                        new JournalEntriesExport($table->getQuery()),
                        'journal-entries-'.now()->format('Y-m-d').'.xlsx'
                    )),
            ])
            ->columns([
                TextColumn::make('entry_number')
                    ->label('Entry #')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('reference_type')
                    ->label('Source')
                    ->formatStateUsing(fn (?string $state, JournalEntry $record): ?string => $state ? "{$state} #{$record->reference_id}" : null)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('lines_count')
                    ->label('Lines')
                    ->counts('lines')
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Posted By')
                    ->placeholder('System')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                    ]),

                SelectFilter::make('reference_type')
                    ->label('Source')
                    ->options(fn (): array => JournalEntry::query()
                        ->whereNotNull('reference_type')
                        ->distinct()
                        ->orderBy('reference_type')
                        ->pluck('reference_type', 'reference_type')
                        ->all()),

                Filter::make('date_range')
                    ->label('Date range')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $from) => $q->whereDate('date', '>=', $from))
                            ->when($data['to'] ?? null, fn (Builder $q, $to) => $q->whereDate('date', '<=', $to));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Post journal entry')
                    ->modalDescription('Posting locks the entry into the ledger and makes it appear in the financial reports. This cannot be undone.')
                    ->visible(fn (JournalEntry $record): bool => $record->status === 'draft'
                        && FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_POST))
                    ->authorize(fn (): bool => FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_POST))
                    ->action(function (JournalEntry $record): void {
                        // Defence in depth: re-check the permission at execution time.
                        if (! FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_POST)) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot post this entry')
                                ->body('You do not have permission to post journal entries.')
                                ->send();

                            return;
                        }

                        try {
                            $record->load('lines');
                            $record->post();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot post this entry')
                                ->body($e->getMessage())
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title("Entry {$record->entry_number} posted.")
                            ->send();
                    }),
            ])
            ->defaultSort('date', 'desc');
    }
}
