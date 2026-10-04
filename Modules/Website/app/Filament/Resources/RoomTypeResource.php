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
use Modules\Website\Filament\Resources\RoomTypeResource\Pages;
use Modules\Website\Models\RoomType;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class RoomTypeResource extends Resource
{
    protected static ?string $model = RoomType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Room Type';

    protected static ?string $pluralModelLabel = 'Room Types';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Room Type')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            // Kept in step with the name so the public site has a
                            // stable URL for the page. Editing an existing slug would
                            // break any link already shared, so it is only offered on
                            // create, which is when the slug is derived.
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', str($state)->slug()->toString());
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the public page URL.'),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('capacity')
                            ->label('Max capacity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('size')->maxLength(255),
                        Forms\Components\TextInput::make('bed_type')->maxLength(255),
                    ])->columns(2),
                Section::make('Occupancy & Extras')
                    ->description('Base occupancy is included in the nightly rate. Extra guest fees are charged on top.')
                    ->schema([
                        Forms\Components\TextInput::make('base_occupancy')
                            ->helperText('Guests included in the price.')
                            ->numeric()
                            ->minValue(1)
                            ->default(2),
                        Forms\Components\TextInput::make('extra_adult_fee')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(0),
                        Forms\Components\TextInput::make('extra_child_fee')
                            ->numeric()
                            ->prefix('₦')
                            ->minValue(0),
                        Forms\Components\Select::make('rate_code_id')
                            ->label('Rate code')
                            // Website pricing can follow a PMS rate code so the public
                            // site and the front desk quote the same thing.
                            ->relationship('rateCode', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Syncs the website price with a front desk rate code.'),
                    ])->columns(2),
                Section::make('Presentation')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Image')
                            ->disk('public')
                            ->directory('room-types')
                            ->image()
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            // The column holds a disk path, so Filament renders it
                            // against the public disk. Pasting a remote URL here
                            // stores the URL verbatim and RoomType::imageUrl() will
                            // pass it through, which is why the hint says so.
                            ->helperText('Stored on the public disk. An existing image URL will keep working until it is replaced.'),
                        Forms\Components\TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\CheckboxList::make('amenities')
                            ->label('Amenities')
                            ->relationship('amenities', 'name')
                            // Preload would render every amenity chip for every room
                            // type, which is a lot of noise for a catalogue this size.
                            ->searchable()
                            ->columns(3)
                            ->bulkToggleable()
                            ->helperText('Shown as the amenity list on the public room page.'),
                    ])->columns(2),
                Section::make('Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive room types are hidden from the public site and cannot be booked.')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Featured room types lead the rooms listing.')
                            ->default(false),
                        Forms\Components\TextInput::make('display_order')
                            ->helperText('Lower numbers appear first.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (RoomType $record): ?string => $record->bed_type),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')->label('Sleeps')->suffix(' guests')->sortable(),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Units')
                    ->counts('units')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amenities.name')
                    ->label('Amenities')
                    ->badge()
                    ->separator(',')
                    ->toggleable()
                    // Long amenity lists wrap the row into a wall of chips; the count
                    // is enough signal in a list, and the full list is on the form.
                    ->limitList(3),
                Tables\Columns\IconColumn::make('is_featured')->label('Featured')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Live')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('display_order')->label('Order')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                // Physical room numbers are still edited through the legacy screen,
                // which handles the units table for the type. The type itself is
                // managed here.
                Actions\Action::make('units')
                    ->label('Units')
                    ->icon('heroicon-o-building-office-2')
                    ->color('gray')
                    ->url(fn (RoomType $record): string => route('website.admin.room-types.show', $record)),
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
            'index' => Pages\ListRoomTypes::route('/'),
            'create' => Pages\CreateRoomType::route('/create'),
            'edit' => Pages\EditRoomType::route('/{record}/edit'),
            'view' => Pages\ViewRoomType::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::ROOM_TYPES_READ,
            WebsitePermission::ROOM_TYPES_UPDATE,
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
        return WebsitePermission::allows(WebsitePermission::ROOM_TYPES_CREATE);
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::ROOM_TYPES_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::ROOM_TYPES_DELETE);
    }
}
