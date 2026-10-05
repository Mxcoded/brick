<?php

namespace Modules\Frontdeskcrm\Filament\Resources\GuestResource\RelationManagers;

use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;

/**
 * Every stay a guest has ever had, on the guest's own screen.
 *
 * This is the panel's answer to the "Stay History" table on the legacy guest
 * card, and to the missing visit column in the directory: `visit_count` on the
 * guest only moves when someone is actively checked out, so a guest with three
 * completed stays and one in house still reads as "2". The rows below are the
 * actual registrations, which is the number the desk is being asked about.
 *
 * Read-only on purpose. Editing or voiding a stay is an operational action on
 * the registration itself, so each row links to the registration rather than
 * duplicating it here.
 */
class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Stay history';

    protected static ?string $recordTitleAttribute = 'reservation_code';

    public function table(Table $table): Table
    {
        return $table
            // The card ordered by arrival, and so does a person looking for the
            // most recent stay.
            ->defaultSort('check_in', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')
                    ->label('Reference')
                    ->weight('bold')
                    ->searchable()
                    ->placeholder('Walk-in')
                    ->copyable(),

                Tables\Columns\TextColumn::make('booking.booking_reference')
                    ->label('Website booking')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Arrival')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Departure')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_of_nights')
                    ->label('Nights')
                    ->alignCenter()
                    ->placeholder('0'),

                Tables\Columns\TextColumn::make('roomUnit.room_number')
                    ->label('Room')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    // room_units has no `name` column, which is what breaks
                    // RegistrationResource when it asks for one.
                    ->searchable(['room_number']),

                Tables\Columns\TextColumn::make('stay_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): string => match ($state) {
                        'draft_by_guest' => 'Draft',
                        'reserved' => 'Reserved',
                        'checked_in' => 'Checked in',
                        'checked_out' => 'Checked out',
                        'no_show' => 'No-show',
                        'cancelled' => 'Cancelled',
                        default => (string) $state,
                    })
                    ->color(static fn (?string $state): string => match ($state) {
                        'checked_in' => 'success',
                        'checked_out' => 'info',
                        'reserved' => 'warning',
                        'draft_by_guest' => 'gray',
                        'no_show', 'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Room total')
                    ->money('NGN')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('folio_balance')
                    ->label('Folio balance')
                    ->money('NGN')
                    ->alignEnd()
                    // Money still owed is what the desk acts on, so it is called
                    // out rather than left inside the registration.
                    ->getStateUsing(static fn (Registration $record): float => (float) $record->folios()
                        ->where('status', 'open')
                        ->sum('balance'))
                    ->color(static fn (float $state): string => $state > 0 ? 'danger' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stay_status')
                    ->label('Status')
                    ->options([
                        'draft_by_guest' => 'Draft',
                        'reserved' => 'Reserved',
                        'checked_in' => 'Checked in',
                        'checked_out' => 'Checked out',
                        'no_show' => 'No-show',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make()
                    // The registration's own Filament page, so the stay history
                    // links into the record rather than a second copy of it.
                    ->url(static fn (Registration $record): string => RegistrationResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No stays on file')
            ->emptyStateDescription('This guest has not been checked in yet.');
    }

    /**
     * Signature matches Filament's RelationManager: the owner record and the
     * page this manager hangs off, not a narrowed type. Narrowing it here
     * makes the class unloadable.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::REGISTRATIONS_READ,
            FrontdeskPermission::GUESTS_READ,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }
}
