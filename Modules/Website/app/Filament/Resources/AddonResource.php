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
use Modules\Website\Filament\Resources\AddonResource\Pages;
use Modules\Website\Models\Addon;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class AddonResource extends Resource
{
    protected static ?string $model = Addon::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Add-on';

    protected static ?string $pluralModelLabel = 'Add-ons';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Add-on')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', str($state)->slug()->toString());
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_per_night')
                            ->label('Charged per night')
                            // An add-on can be a flat fee for the stay (airport
                            // transfer) or a nightly one (breakfast). The booking
                            // totals read this flag, so the wording matters.
                            ->helperText('On when the price is per night, off for a one-off charge.')
                            ->default(false),
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->helperText('Icon name shown in the booking extras list.'),
                        Forms\Components\TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive add-ons cannot be added to a new booking.')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->helperText('Lower numbers appear first.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(70)->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('is_per_night')
                    ->label('Basis')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Per night' : 'One-off')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Live')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Used on')
                    ->counts('bookings')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_per_night')->label('Charged per night'),
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
            'index' => Pages\ListAddons::route('/'),
            'create' => Pages\CreateAddon::route('/create'),
            'edit' => Pages\EditAddon::route('/{record}/edit'),
            'view' => Pages\ViewAddon::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::ADDONS_READ,
            WebsitePermission::ADDONS_UPDATE,
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
        return WebsitePermission::allows(WebsitePermission::ADDONS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::ADDONS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::ADDONS_DELETE);
    }
}
