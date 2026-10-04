<?php

namespace Modules\Website\Filament\Resources\ContactMessageResource\RelationManagers;

use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Website\Support\WebsitePermission;

/**
 * The reply thread on an enquiry.
 *
 * Read-only by design: composing and sending an email is still the legacy
 * controller's job, because that action dispatches the mail and stamps
 * `last_reply_at` in the same transaction that writes the reply row. Reimplementing
 * it here would mean two code paths writing the same rows, so this panel shows the
 * history and links out to the screen that can send.
 */
class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    protected static ?string $title = 'Replies';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('direction')
                    ->badge()
                    // outgoing is a staff reply, incoming is the guest's answer.
                    // Named here because "outgoing" reads ambiguously on a thread
                    // whose direction is relative to the hotel.
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'incoming' => 'From guest',
                        'outgoing' => 'To guest',
                        default => (string) $state,
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff')
                    ->placeholder('System')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('message')->wrap()->limit(200),
                Tables\Columns\IconColumn::make('is_read')->label('Read')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Sent')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->headerActions([
                Actions\Action::make('reply')
                    ->label('Reply to guest')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('primary')
                    ->url(fn (): string => route('website.admin.contact-messages.reply', $this->ownerRecord))
                    ->visible(fn (): bool => WebsitePermission::allows(WebsitePermission::CONTACT_MESSAGES_UPDATE)),
            ]);
    }

    /**
     * No create form on this panel, so the header action is never offered.
     */
    protected function canCreate(): bool
    {
        return false;
    }

    /**
     * Delete is withheld too. A reply is part of the record of what was told to a
     * guest, so removing one is not a per-row decision to make in a table.
     */
    protected function canDelete($record): bool
    {
        return false;
    }
}
