<?php

namespace Modules\Website\Filament\Resources\BookingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Website\Filament\Resources\BookingResource;
use Modules\Website\Models\Booking;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Confirming, cancelling, marking paid, resending the confirmation and
            // assigning a room are POST actions on the legacy controller. They carry
            // side effects a read-only panel action cannot reproduce - a ledger
            // settlement on payment, a guest email on confirm and cancel, an
            // availability recheck on assignment - so they stay there. This link keeps
            // the booking and its operational actions one hop apart.
            Actions\Action::make('operations')
                ->label('Confirm / cancel / assign')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray')
                ->url(fn (): string => route('website.admin.bookings.show', $this->record))
                ->visible(fn (Booking $record): bool => in_array($record->status, [
                    'pending',
                    'confirmed',
                    'checked_in',
                ], true)),
            Actions\EditAction::make(),
        ];
    }
}
