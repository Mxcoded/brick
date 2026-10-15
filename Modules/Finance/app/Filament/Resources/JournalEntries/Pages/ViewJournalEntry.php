<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Services\JournalEntryService;
use Modules\Finance\Support\FinancePermission;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        $entry = $this->getRecord();

        return [
            Actions\Action::make('reverse')
                ->label('Reverse entry')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reverse this journal entry?')
                ->modalDescription('A mirror-image entry is created and posted. The original is kept for the audit trail and cannot be reversed twice.')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->label('Reversal date')
                        ->native(false)
                        ->default($entry->date)
                        ->required()
                        ->helperText('Must fall in an open accounting period.'),
                    Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->rows(2)
                        ->maxLength(255),
                ])
                ->action(function (array $data) use ($entry): void {
                    try {
                        $reversal = app(JournalEntryService::class)->reverse(
                            $entry,
                            $data['date'] ?? null,
                            $data['reason'] ?? null,
                        );
                    } catch (\Throwable $e) {
                        Notifications\Notification::make()
                            ->title('Could not reverse entry')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notifications\Notification::make()
                        ->title('Reversal posted')
                        ->body("{$reversal->entry_number} cancels {$entry->entry_number}.")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $reversal]));
                })
                ->visible(fn (): bool => $entry->status === 'posted'
                    && ! $entry->isReversed()
                    && FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_REVERSE)),

            Actions\Action::make('post')
                ->label('Post entry')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($entry): void {
                    try {
                        $entry->post();
                    } catch (\Throwable $e) {
                        Notifications\Notification::make()
                            ->title('Could not post entry')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notifications\Notification::make()
                        ->title('Journal entry posted')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $entry->fresh()]));
                })
                ->visible(fn (): bool => $entry->status === 'draft'
                    && $entry->lines()->count() >= 2
                    && $entry->isBalanced()
                    && FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_POST)),

            Actions\EditAction::make()
                ->visible(fn (): bool => $this->getResource()::canEdit($entry)),

            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->getResource()::canDelete($entry)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function infolist(Schema $schema): Schema
    {
        $entry = $this->getRecord();

        return $schema
            ->components([
                Section::make('Entry')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('entry_number')
                            ->label('Entry #')
                            ->fontFamily('mono')
                            ->weight('bold'),

                        TextEntry::make('date')
                            ->date('d M Y'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'posted' ? 'success' : 'gray'),

                        TextEntry::make('description')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('reference_type')
                            ->label('Source')
                            ->formatStateUsing(fn (?string $state, $record): ?string => $state ? "{$state} #{$record->reference_id}" : null)
                            ->placeholder('Manual entry')
                            ->visible(fn (): bool => (bool) $entry->reference_type),

                        TextEntry::make('createdBy.name')
                            ->label('Posted By')
                            ->placeholder('System'),

                        TextEntry::make('reverses.entry_number')
                            ->label('Reverses')
                            ->placeholder('—')
                            ->visible(fn (): bool => $entry->isReversal()),

                        TextEntry::make('reversal.entry_number')
                            ->label('Reversed by')
                            ->placeholder('—')
                            ->color('danger')
                            ->visible(fn (): bool => $entry->status === 'posted' && ! $entry->isReversed()),
                    ]),

                Section::make('Lines')
                    ->heading('Journal Lines')
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('account.code')
                                    ->label('GL Code')
                                    ->fontFamily('mono'),

                                TextEntry::make('account.name')
                                    ->label('Account'),

                                TextEntry::make('party_label')
                                    ->label('Sub-ledger')
                                    ->placeholder('—'),

                                TextEntry::make('debit')
                                    ->numeric()
                                    ->placeholder('—')
                                    ->formatStateUsing(fn ($state): ?string => (float) $state > 0 ? number_format((float) $state, 2) : null),

                                TextEntry::make('credit')
                                    ->numeric()
                                    ->placeholder('—')
                                    ->formatStateUsing(fn ($state): ?string => (float) $state > 0 ? number_format((float) $state, 2) : null),
                            ])
                            ->columns(5),
                    ]),

                Section::make('Totals')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_debit')
                            ->label('Total Debit')
                            ->state(fn (): float => (float) $entry->lines->sum('debit'))
                            ->numeric()
                            ->fontFamily('mono'),

                        TextEntry::make('total_credit')
                            ->label('Total Credit')
                            ->state(fn (): float => (float) $entry->lines->sum('credit'))
                            ->numeric()
                            ->fontFamily('mono'),

                        TextEntry::make('balance_status')
                            ->label('Balance')
                            ->state(fn (): array => [
                                'label' => $entry->isBalanced() ? 'Balanced' : 'Out of balance',
                                'color' => $entry->isBalanced() ? 'success' : 'danger',
                                'icon' => $entry->isBalanced() ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle',
                            ])
                            ->badge(),
                    ]),
            ]);
    }
}
