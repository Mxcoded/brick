<?php

namespace Modules\Website\Filament\Resources\NewsletterResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Website\Filament\Resources\NewsletterResource;

class CreateNewsletter extends CreateRecord
{
    protected static string $resource = NewsletterResource::class;
}
