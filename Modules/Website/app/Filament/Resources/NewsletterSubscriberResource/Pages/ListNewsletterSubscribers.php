<?php

namespace Modules\Website\Filament\Resources\NewsletterSubscriberResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\NewsletterSubscriberResource;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        // Import and export are declared on the table, so they already sit in this
        // page's header.
        return [];
    }
}
