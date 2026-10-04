<?php

namespace Modules\Website\Filament\Resources\ContactMessageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Website\Filament\Resources\ContactMessageResource;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Reply and archive are POST routes on the legacy controller. Replying
            // dispatches the email and stamps last_reply_at in the same transaction
            // that writes the reply row, and archiving records who did it. Both stay
            // there; the reply thread below reads what they wrote.
            //
            // These are page header actions, not table actions, so the record comes
            // from $this->record rather than being injected into the closure.
            Actions\Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->url(fn (): string => route('website.admin.contact-messages.reply', $this->record)),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->visible(fn (): bool => ! $this->record->is_archived)
                ->url(fn (): string => route('website.admin.contact-messages.archive', $this->record)),
            Actions\EditAction::make(),
        ];
    }
}
