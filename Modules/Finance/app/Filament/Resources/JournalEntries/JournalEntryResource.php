<?php

namespace Modules\Finance\Filament\Resources\JournalEntries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\CreateJournalEntry;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\EditJournalEntry;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use Modules\Finance\Filament\Resources\JournalEntries\Pages\ViewJournalEntry;
use Modules\Finance\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Finance\Filament\Resources\JournalEntries\Tables\JournalEntriesTable;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Support\FinancePermission;
use UnitEnum;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Journal Entries';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'journal entry';

    protected static ?string $pluralModelLabel = 'journal entries';

    protected static ?string $recordTitleAttribute = 'entry_number';

    protected static ?string $slug = 'journal-entries';

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return FinancePermission::allowsAny(
            FinancePermission::JOURNAL_ENTRIES_READ,
            FinancePermission::JOURNAL_ENTRIES_CREATE,
            FinancePermission::JOURNAL_ENTRIES_POST,
            FinancePermission::JOURNAL_ENTRIES_REVERSE,
        );
    }

    public static function canViewAny(): bool
    {
        return FinancePermission::allowsAny(
            FinancePermission::JOURNAL_ENTRIES_READ,
            FinancePermission::JOURNAL_ENTRIES_CREATE,
            FinancePermission::JOURNAL_ENTRIES_POST,
            FinancePermission::JOURNAL_ENTRIES_REVERSE,
        );
    }

    public static function canView($record): bool
    {
        // Posting and reversing require reading the entry being acted on, so
        // those grants imply visibility even if plain read was not granted.
        return FinancePermission::allowsAny(
            FinancePermission::JOURNAL_ENTRIES_READ,
            FinancePermission::JOURNAL_ENTRIES_POST,
            FinancePermission::JOURNAL_ENTRIES_REVERSE,
        );
    }

    public static function canCreate(): bool
    {
        return FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_CREATE);
    }

    public static function canEdit($record): bool
    {
        return $record->status === 'draft'
            && FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_UPDATE);
    }

    /**
     * A posted entry is never deleted - it is reversed instead, so the audit
     * trail keeps the original document. Deletion is only ever for drafts.
     */
    public static function canDelete($record): bool
    {
        return $record->status === 'draft'
            && FinancePermission::allows(FinancePermission::JOURNAL_ENTRIES_DELETE);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'view' => ViewJournalEntry::route('/{record}'),
            'edit' => EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
