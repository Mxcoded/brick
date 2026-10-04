<?php

namespace Modules\Website\Filament\Resources\TestimonialResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Website\Filament\Resources\TestimonialResource;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The printable Wi-Fi QR card is still a Blade screen, keyed by branch
            // rather than by review, so it has no natural home in this table. Link
            // to it from the header rather than letting staff find the old admin.
            Actions\Action::make('wifiQr')
                ->label('Wi-Fi QR cards')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->url(fn (): string => route('website.admin.testimonials.wifi-qr')),
            Actions\CreateAction::make(),
        ];
    }
}
