<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Finance\Models\JournalEntry;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected bool $shouldPost = false;

    public function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    /**
     * Drafts are saved by default; posting is opt-in so a half-finished entry
     * can be parked and finished later.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            Action::make('save')
                ->label('Save draft')
                ->action('create'),

            Action::make('saveAndPost')
                ->label('Save & post')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('createAndPost'),
        ];
    }

    public function createAndPost(): void
    {
        $this->shouldPost = true;

        $this->create();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): JournalEntry
    {
        $shouldPost = $this->shouldPost;

        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::nextEntryNumber(),
            'date' => $data['date'],
            'description' => $data['description'],
            'reference_type' => $data['reference_type'] ?? 'manual',
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $entry->lines()->createMany($this->normaliseLines($data['lines'] ?? []));

        if ($shouldPost) {
            $this->postEntry($entry);
        }

        return $entry->fresh();
    }

    protected function postEntry(JournalEntry $entry): void
    {
        try {
            $entry->post();
        } catch (\Throwable $e) {
            // Keep the draft so the user can correct it instead of losing the work.
            throw ValidationException::withMessages([
                'data.lines' => $e->getMessage(),
            ]);
        }

        Notification::make()
            ->title('Journal entry posted')
            ->success()
            ->send();
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    protected function normaliseLines(array $lines): array
    {
        return collect($lines)
            ->map(fn (array $line): array => [
                'account_id' => $line['account_id'],
                'debit' => round((float) ($line['debit'] ?? 0), 2),
                'credit' => round((float) ($line['credit'] ?? 0), 2),
                'description' => $line['description'] ?? null,
            ])
            ->all();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
