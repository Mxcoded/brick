<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\Pages;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Registration';

    protected static ?string $pluralModelLabel = 'Registrations';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Guest & Booking')
                    ->schema([
                        Forms\Components\Select::make('guest_id')
                            ->relationship('guest', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('booking_id')
                            ->relationship('booking', 'booking_reference')
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('booking_source_id')
                            ->relationship('bookingSource', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('guest_type_id')
                            ->relationship('guestType', 'name')
                            ->nullable(),
                        Forms\Components\TextInput::make('reservation_code')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('full_name')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('contact_number')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Room & Rate')
                    ->schema([
                        Forms\Components\Select::make('room_type_id')
                            ->relationship('roomType', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('room_unit_id')
                            ->relationship('roomUnit', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('rate_code_id')
                            ->relationship('rateCode', 'code')
                            ->nullable(),
                        Forms\Components\TextInput::make('room_rate')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('no_of_guests')
                            ->numeric()
                            ->default(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Stay Details')
                    ->schema([
                        Forms\Components\DatePicker::make('check_in')
                            ->required(),
                        Forms\Components\DatePicker::make('check_out')
                            ->required(),
                        Forms\Components\Select::make('stay_status')
                            ->options([
                                'reserved' => 'Reserved',
                                'checked_in' => 'Checked In',
                                'checked_out' => 'Checked Out',
                                'no_show' => 'No Show',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('reserved')
                            ->required(),
                        Forms\Components\Select::make('billing_type')
                            ->options([
                                'standard' => 'Standard',
                                'city_ledger' => 'City Ledger',
                                'company' => 'Company',
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('no_of_nights')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('checked_in_at')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('actual_checkout_at')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guest.full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roomType.name')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roomUnit.name')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_of_nights')
                    ->numeric(),
                Tables\Columns\TextColumn::make('stay_status')
                    ->badge()
                    ->colors([
                        'warning' => 'reserved',
                        'success' => 'checked_in',
                        'gray' => 'checked_out',
                        'danger' => ['no_show', 'cancelled'],
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stay_status')
                    ->options([
                        'reserved' => 'Reserved',
                        'checked_in' => 'Checked In',
                        'checked_out' => 'Checked Out',
                        'no_show' => 'No Show',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
            'view' => Pages\ViewRegistration::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::REGISTRATIONS_READ,
            FrontdeskPermission::REGISTRATIONS_MANAGE,
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

    public static function canCreate(): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::REGISTRATIONS_CREATE,
            FrontdeskPermission::REGISTRATIONS_MANAGE,
        );
    }

    public static function canEdit($record): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::REGISTRATIONS_UPDATE,
            FrontdeskPermission::REGISTRATIONS_MANAGE,
        );
    }

    public static function canDelete($record): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::REGISTRATIONS_DELETE,
            FrontdeskPermission::REGISTRATIONS_MANAGE,
        );
    }
}
