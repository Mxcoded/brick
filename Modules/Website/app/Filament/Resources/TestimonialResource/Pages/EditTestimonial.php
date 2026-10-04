<?php

namespace Modules\Website\Filament\Resources\TestimonialResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Website\Filament\Resources\TestimonialResource;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
