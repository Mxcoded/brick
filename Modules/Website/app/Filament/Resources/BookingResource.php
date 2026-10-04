<?php

namespace Modules\Website\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Website\Filament\Resources\BookingResource\Pages;
use Modules\Website\Models\Booking;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $pluralModelLabel = 'Bookings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stay')
                    ->schema([
                        Forms\Components\Select::make('room_type_id')
                            ->relationship('roomType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('room_unit_id')
                            ->relationship('roomUnit', 'room_number')
                            ->searchable()
                            ->preload()
                            // A unit is chosen before check-in only when the desk
                            // assigns it, so the field starts empty and stays optional.
                            ->helperText('Leave empty until the room is assigned at check-in.'),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Check-in')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('Check-out')
                            ->required()
                            ->native(false)
                            ->after('check_in_date'),
                        Forms\Components\TextInput::make('adults')->numeric()->minValue(1)->maxValue(10)->required(),
                        Forms\Components\TextInput::make('children')->numeric()->minValue(0)->maxValue(10)->default(0),
                    ])->columns(2),
                Section::make('Guest')
                    ->schema([
                        Forms\Components\TextInput::make('guest_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('guest_email')->email()->required()->maxLength(255),
                        Forms\Components\TextInput::make('guest_phone')->tel()->maxLength(255),
                        Forms\Components\Textarea::make('special_requests')->rows(3)->columnSpanFull(),
                    ])->columns(2),
                Section::make('Status & Payment')
                    ->schema([
                        // The legacy controller refuses to move a booking backwards
                        // through the status flow, so the option list is ordered by
                        // that flow rather than alphabetically here.
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'checked_in' => 'Checked In',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'paystack' => 'Paystack',
                                'flutterwave' => 'Flutterwave',
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'card' => 'Card',
                                'other' => 'Other',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('source')->maxLength(255),
                    ])->columns(2),
                Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()->prefix('₦')->required(),
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()->prefix('₦')->required(),
                        Forms\Components\Textarea::make('admin_notes')->rows(3)->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_reference')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('guest_email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('roomType.name')->label('Room Type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('check_in_date')->label('Check-in')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('check_out_date')->label('Check-out')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('adults')->label('Guests')
                    ->formatStateUsing(fn ($state, $record): string => $state.'A'.($record->children ? ' / '.$record->children.'C' : '')),
                Tables\Columns\TextColumn::make('total_amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->money('NGN')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'checked_in' => 'Checked In',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('check_in_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Checking in from'),
                        Forms\Components\DatePicker::make('until')->label('Checking in until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q) => $q->whereDate('check_in_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('check_in_date', '<=', $data['until']))
                    ),
            ])
            ->actions([
                // Confirming, cancelling, marking paid and room assignment are POST
                // routes on the legacy controller that also post to the ledger and
                // send guest mail. A table action issues a GET, so linking one here
                // would only produce a 405, and reimplementing them would give the
                // same state change two different side effects. They stay on the
                // booking screen in the legacy admin, linked from the Filament view
                // page. Editing status and the assigned unit below is the CRUD path.
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::BOOKINGS_READ,
            WebsitePermission::BOOKINGS_UPDATE,
        );
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    /**
     * Gatekeeping `create` is what keeps the permission meaningful. The capability
     * stays in the taxonomy because `admin` holds `*`, but a role that cannot
     * reach the panel for creating bookings will not find one here: a booking
     * comes from a guest completing checkout on the public site.
     */
    public static function canCreate(): bool
    {
        return WebsitePermission::allows(WebsitePermission::BOOKINGS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::BOOKINGS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::BOOKINGS_DELETE);
    }
}
