<?php

namespace Modules\Website\Filament\Resources\NewsletterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\NewsletterResource;

class ListNewsletters extends ListRecords
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
