<?php

namespace Modules\Website\Filament\Resources\NewsletterResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Website\Filament\Resources\NewsletterResource;
use Modules\Website\Models\Newsletter;
use Modules\Website\Support\WebsitePermission;

class ViewNewsletter extends ViewRecord
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        // Page header actions, not table actions: the record comes from $this->record
        // and is not injected into these closures.
        return [
            // Duplicating is a model method that copies subject, preview text and
            // content into a fresh draft. Safe to run from the panel, and it saves
            // staff a POST round trip to the legacy screen.
            Actions\Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => WebsitePermission::allows(WebsitePermission::NEWSLETTER_CREATE))
                ->action(function (): void {
                    $copy = $this->record->duplicate();

                    Notification::make()
                        ->success()
                        ->title('Newsletter duplicated')
                        ->body('A draft copy is ready to edit and send.')
                        ->send();

                    $this->redirect(NewsletterResource::getUrl('edit', ['record' => $copy]));
                }),
            // Sending walks every active subscriber, writes a delivery log per address
            // and sends synchronously inside the legacy controller. It is a POST, so
            // link to the campaign screen there rather than open with a GET and get a
            // 405.
            Actions\Action::make('send')
                ->label('Send campaign')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (): bool => $this->record->canSend())
                ->url(fn (): string => route('website.admin.newsletter.campaigns.show', $this->record)),
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn (): string => route('website.admin.newsletter.campaigns.preview', $this->record)),
            Actions\Action::make('deliveryStatus')
                ->label('Delivery status')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->visible(fn (): bool => in_array($this->record->status, [
                    Newsletter::STATUS_SENT,
                    Newsletter::STATUS_SENDING,
                    Newsletter::STATUS_FAILED,
                ], true))
                ->url(fn (): string => route('website.admin.newsletter.campaigns.delivery-status', $this->record)),
            Actions\EditAction::make(),
        ];
    }
}
