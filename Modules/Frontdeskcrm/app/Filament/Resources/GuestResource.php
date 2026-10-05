<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\RelationManagers\RegistrationsRelationManager;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Frontdeskcrm\Support\GuestGender;
use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestNationality;
use Modules\Frontdeskcrm\Support\GuestTitle;
use UnitEnum;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Guest';

    protected static ?string $pluralModelLabel = 'Guests';

    protected static ?int $navigationSort = 1;

    /**
     * guests columns are varchar(191) throughout, so the form has to cap at the
     * same width or a long name fails on insert rather than being truncated.
     */
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Select::make('title')
                            ->label('Title')
                            ->options(GuestTitle::options())
                            ->placeholder('Not stated')
                            ->native(false)
                            ->searchable()
                            // Legacy rows hold the period-less spelling, which
                            // is no longer an option key and would render blank.
                            ->formatStateUsing(static fn (?string $state): ?string => GuestTitle::normalise($state))
                            ->dehydrateStateUsing(static fn (?string $state): ?string => GuestTitle::normalise($state))
                            ->nullable(),
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Select::make('gender')
                            ->options(GuestGender::options())
                            ->placeholder('Not stated')
                            ->native(false)
                            // Older rows hold the lowercase spelling the public
                            // booking form used to write, which no longer
                            // matches an option key and would render as blank
                            // in the select.
                            ->formatStateUsing(static fn (?string $state): ?string => GuestGender::normalise($state))
                            ->dehydrateStateUsing(static fn (?string $state): ?string => GuestGender::normalise($state))
                            ->nullable(),
                        Forms\Components\DatePicker::make('birthday')
                            ->label('Date of Birth')
                            ->maxDate(now())
                            ->nullable(),
                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(191)
                            ->helperText('Country name, e.g. Nigeria. "Nigerian" and "USA" are recognised and stored canonically.')
                            ->formatStateUsing(static fn (?string $state): ?string => GuestNationality::normalise($state))
                            ->dehydrateStateUsing(static fn (?string $state): ?string => GuestNationality::normalise($state))
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Identification')
                    ->description('Shown on the guest card at check-in.')
                    ->schema([
                        // Deliberately a free-text field: identification_type is a
                        // Still free text, deliberately: guests can hold a document the hotel has no
                        // name for, and a closed select would blank it on save.
                        // A recognised spelling is still reduced to the
                        // canonical value so the public booking form's ID-number
                        // format check applies whatever screen recorded it.
                        Forms\Components\TextInput::make('identification_type')
                            ->label('ID Type')
                            ->maxLength(191)
                            ->helperText('For example '.implode(', ', array_keys(GuestIdentificationType::options())).'.')
                            ->formatStateUsing(static fn (?string $state): ?string => GuestIdentificationType::normalise($state) ?? $state)
                            ->dehydrateStateUsing(static fn (?string $state): ?string => GuestIdentificationType::normalise($state) ?? $state)
                            ->nullable(),
                        Forms\Components\TextInput::make('identification_number')
                            ->label('ID Number')
                            ->maxLength(191)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Contact Details')
                    ->schema([
                        Forms\Components\TextInput::make('contact_number')
                            ->label('Phone')
                            ->tel()
                            // NOT NULL in the schema, so it cannot be optional here
                            // however convenient that would be.
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\Textarea::make('home_address')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\TextInput::make('zip_code')
                            ->label('Postal Code')
                            ->maxLength(191)
                            ->nullable(),
                    ])
                    ->columns(3),

                Section::make('Professional')
                    ->schema([
                        Forms\Components\TextInput::make('occupation')
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company')
                            ->maxLength(191)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Emergency Contact')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_name')
                            ->label('Name')
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\TextInput::make('emergency_relationship')
                            ->label('Relationship')
                            ->maxLength(191)
                            ->nullable(),
                        Forms\Components\TextInput::make('emergency_contact')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(191)
                            ->nullable(),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Section::make('Preferences')
                    ->schema([
                        Forms\Components\Toggle::make('opt_in_data_save')
                            ->label('Save data for faster future check-in')
                            ->helperText('Keeps the guest profile for repeat bookings.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // The directory's whole point is the visit history, so the count has
            // to come from the records rather than from the denormalised
            // visit_count, which only moves when a stay is checked out.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('registrations'))
            ->defaultSort('last_visit_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Guest')
                    ->weight('bold')
                    ->searchable(['full_name', 'title', 'email', 'contact_number'])
                    ->description(fn (Guest $record): ?string => $record->company_name ?: null)
                    ->wrap(),

                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Stays')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn (?int $state): string => ($state ?? 0) > 0 ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('visit_count')
                    ->label('Completed visits')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    // More than one completed stay is what the desk means by a
                    // returning guest.
                    ->color(fn (?int $state): string => ($state ?? 0) > 1 ? 'success' : 'gray')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),

                Tables\Columns\TextColumn::make('last_visit_at')
                    ->label('Last visit')
                    ->date('M d, Y')
                    ->sortable()
                    ->placeholder('Never visited'),

                Tables\Columns\TextColumn::make('contact_number')
                    ->label('Phone')
                    ->icon('heroicon-m-phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('identification_type')
                    ->label('ID')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nationality')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Guest since')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options(GuestGender::options())
                    // Setting a query callback replaces Filament's own
                    // equality filter, so the blank case has to be handled here
                    // or an unfiltered table would filter itself to nothing.
                    //
                    // Compared with lower() rather than by exact value, because
                    // rows written before GuestGender existed hold the lowercase
                    // spelling and equality would silently drop them.
                    ->query(static function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereRaw('lower(gender) = ?', [mb_strtolower((string) $value)]);
                    }),
                Tables\Filters\TernaryFilter::make('returning')
                    ->label('Returning guests')
                    ->placeholder('All')
                    ->trueLabel('More than one completed stay')
                    ->falseLabel('One stay or none')
                    ->query(fn (Builder $query, array $data): Builder => match (true) {
                        ($data['state'] ?? null) === true => $query->where('visit_count', '>', 1),
                        ($data['state'] ?? null) === false => $query->where('visit_count', '<=', 1),
                        default => $query,
                    }),
                Tables\Filters\Filter::make('has_stays')
                    ->label('Has a stay on file')
                    ->query(fn (Builder $query): Builder => $query->whereHas('registrations')),
                Tables\Filters\Filter::make('never_visited')
                    ->label('Never checked out')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_visit_at')),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    // The legacy controller refused to delete a guest who had ever
                    // checked in; without this the same action silently strips the
                    // stay history off the directory.
                    ->before(static::guardAgainstDeletingGuestsWithStays()),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->before(static::guardAgainstDeletingGuestsWithStays()),
                ]),
            ]);
    }

    /**
     * The legacy guest card, as a Filament infolist.
     *
     * The Blade card showed total stays, total nights, first visit and last
     * visit alongside the identity and emergency blocks. `total_nights` was
     * summed off registrations, but registrations has no such column - the
     * field is `no_of_nights` - so that figure came back as a database error
     * rather than a number.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Stay history')
                    ->description('Counted from the registrations themselves.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('stay_total')
                            ->label('Total stays')
                            ->icon('heroicon-m-calendar-days')
                            ->getStateUsing(fn (Guest $record): int => $record->registrations()->count()),
                        TextEntry::make('stay_nights')
                            ->label('Total nights')
                            ->icon('heroicon-m-moon')
                            ->getStateUsing(fn (Guest $record): int => (int) $record->registrations()->sum('no_of_nights')),
                        TextEntry::make('first_visit')
                            ->label('First visit')
                            ->placeholder('—')
                            ->getStateUsing(fn (Guest $record) => $record->registrations()->min('check_in')),
                        TextEntry::make('last_visit_at')
                            ->label('Last visit')
                            ->placeholder('Never')
                            ->date('M d, Y'),
                        TextEntry::make('visit_count')
                            ->label('Completed stays')
                            ->badge()
                            ->color(fn (?int $state): string => ($state ?? 0) > 1 ? 'success' : 'gray'),
                    ]),

                Section::make('Guest information')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('full_name')
                            ->weight('bold')
                            ->getStateUsing(fn (Guest $record): string => trim($record->title.' '.$record->full_name)),
                        TextEntry::make('gender')->placeholder('Not stated')->badge(),
                        TextEntry::make('birthday')->date('F d, Y')->placeholder('—'),
                        TextEntry::make('nationality')->placeholder('—'),
                        TextEntry::make('identification_type')
                            ->label('ID type')
                            ->placeholder('—'),
                        TextEntry::make('identification_number')
                            ->label('ID number')
                            ->placeholder('—'),
                    ]),

                Section::make('Contact')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('contact_number')->label('Phone')->icon('heroicon-m-phone')->copyable(),
                        TextEntry::make('email')->label('Email')->icon('heroicon-m-envelope')->placeholder('—')->copyable(),
                        TextEntry::make('home_address')->label('Address')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('city')
                            ->placeholder('—')
                            ->getStateUsing(fn (Guest $record): string => collect([
                                $record->city,
                                $record->state,
                                $record->zip_code,
                            ])->filter()->implode(', ')),
                    ]),

                Section::make('Professional')
                    ->columnSpan(3)
                    ->schema([
                        TextEntry::make('occupation')->placeholder('—'),
                        TextEntry::make('company_name')->label('Company')->placeholder('Individual guest'),
                    ])
                    ->columns(2),

                Section::make('Emergency contact')
                    ->columnSpan(3)
                    ->icon('heroicon-m-shield-exclamation')
                    ->schema([
                        TextEntry::make('emergency_name')->label('Name')->placeholder('—'),
                        TextEntry::make('emergency_relationship')->label('Relationship')->placeholder('—'),
                        TextEntry::make('emergency_contact')->label('Phone')->placeholder('—'),
                    ])
                    ->columns(3)
                    // Hidden entirely rather than shown as three dashes.
                    ->visible(fn (Guest $record): bool => filled($record->emergency_name)
                        || filled($record->emergency_contact)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RegistrationsRelationManager::class,
        ];
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

    /**
     * Refuse a delete that would take a guest's stays with it.
     *
     * Shared by the row and bulk actions so the two cannot drift.
     */
    protected static function guardAgainstDeletingGuestsWithStays(): callable
    {
        return function (Guest $record): void {
            if (! $record->registrations()->exists()) {
                return;
            }

            Notification::make()
                ->danger()
                ->title('This guest has stays on file')
                ->body("{$record->full_name} has registrations, so deleting the profile would orphan them. Delete the stays first, or remove the guest from the legacy directory.")
                ->persistent()
                ->send();

            // Thrown rather than $this->halt(): a `before` closure is invoked
            // without the action bound to $this, so the halt() call has no
            // object context to run against.
            throw new Halt;
        };
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
