<?php

namespace Modules\Website\Filament\Resources\ContactMessageResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\ContactMessageResource;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        // No CreateAction: enquiries arrive from the public form and
        // ContactMessageResource::canCreate() is false.
        return [];
    }
}
