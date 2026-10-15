<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Finance\Models\JournalEntry;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected bool $shouldPost = false;

    public function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['lines'] = $this->record->lines()->get()
            ->map(fn ($line): array => [
                'account_id' => $line->account_id,
                'party' => JournalEntryForm::partyValue($line->party_type, $line->party_id),
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'description' => $line->description,
            ])
            ->all();

        return $data;
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            Action::make('save')
                ->label('Save draft')
                ->action('save'),

            Action::make('saveAndPost')
                ->label('Save & post')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('saveAndPost'),
        ];
    }

    public function saveAndPost(): void
    {
        $this->shouldPost = true;

        $this->save();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): JournalEntry
    {
        $record->fill([
            'date' => $data['date'],
            'description' => $data['description'],
            'reference_type' => $data['reference_type'] ?? 'manual',
        ]);

        $lines = collect($data['lines'] ?? [])
            ->map(fn (array $line): array => JournalEntryForm::normaliseLine($line))
            ->values();

        $record->lines()->delete();
        $record->lines()->createMany($lines->all());
        $record->save();

        if ($this->shouldPost) {
            $this->postEntry($record->fresh());
        }

        return $record->fresh();
    }

    protected function postEntry(JournalEntry $entry): void
    {
        try {
            $entry->post();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'data.lines' => $e->getMessage(),
            ]);
        }

        Notification::make()
            ->title('Journal entry posted')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
