<?php

namespace Modules\Website\Filament\Resources\NewsletterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Website\Filament\Resources\NewsletterResource;

class EditNewsletter extends EditRecord
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
