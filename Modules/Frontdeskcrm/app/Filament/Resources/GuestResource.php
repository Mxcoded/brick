<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Guest';

    protected static ?string $pluralModelLabel = 'Guests';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('full_name')->required()->maxLength(255),
                        Forms\Components\Select::make('gender')->options(['Male' => 'Male', 'Female' => 'Female'])->nullable(),
                        Forms\Components\DatePicker::make('birthday')->nullable(),
                        Forms\Components\TextInput::make('nationality')->maxLength(255)->nullable(),
                    ]),
                Section::make('Contact Details')
                    ->schema([
                        Forms\Components\TextInput::make('email')->email()->maxLength(255)->nullable(),
                        Forms\Components\TextInput::make('contact_number')->maxLength(255)->nullable(),
                        Forms\Components\Textarea::make('home_address')->maxLength(500)->nullable()->columnSpanFull(),
                    ]),
                Section::make('Professional & Emergency')
                    ->schema([
                        Forms\Components\TextInput::make('occupation')->maxLength(255)->nullable(),
                        Forms\Components\TextInput::make('company_name')->maxLength(255)->nullable(),
                        Forms\Components\TextInput::make('emergency_name')->maxLength(255)->nullable(),
                        Forms\Components\TextInput::make('emergency_contact')->maxLength(255)->nullable(),
                        Forms\Components\TextInput::make('emergency_relationship')->maxLength(255)->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('contact_number')->searchable(),
                Tables\Columns\TextColumn::make('gender')->badge(),
                Tables\Columns\TextColumn::make('nationality')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')->options(['Male' => 'Male', 'Female' => 'Female']),
            ])
            ->actions([
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
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
            'view' => Pages\ViewGuest::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::GUESTS_READ,
            FrontdeskPermission::GUESTS_MANAGE,
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
            FrontdeskPermission::GUESTS_CREATE,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }

    public static function canEdit($record): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::GUESTS_UPDATE,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }

    public static function canDelete($record): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::GUESTS_DELETE,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }
}
