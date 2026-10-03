<?php

namespace Modules\Restaurant\Filament\Resources\CollectionPoints;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Restaurant\Models\CollectionPoint;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Physical terminals money is collected on: a Monipoint at the front desk, a POS
 * box at the cafe, a separate till in the grill. Different from a sales point.
 */
class CollectionPointResource extends Resource
{
    protected static ?string $model = CollectionPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Collection Points';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'collection point';

    protected static ?string $pluralModelLabel = 'collection points';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/collection-points';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(191)
                ->placeholder('Monipoint'),

            TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->helperText('Short identifier, e.g. MONI or TILL1.'),

            Select::make('asset_account_code')
                ->label('Asset account')
                ->options(fn () => static::assetAccountOptions())
                ->placeholder('Use payment-method mapping')
                ->helperText('Override which bank/till this terminal posts to.'),

            Toggle::make('is_default')
                ->label('Default collection point')
                ->helperText('Used if a shift does not specify a terminal.')
                ->afterStateUpdated(function ($state, $set, $livewire) {
                    if (! $state) {
                        return;
                    }

                    CollectionPoint::query()->whereKeyNot($livewire->record?->getKey())->update(['is_default' => false]);
                }),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->helperText('Inactive terminals disappear from the waiter terminal.'),

            Textarea::make('description')
                ->label('Description')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function assetAccountOptions(): array
    {
        return ChartOfAccount::query()
            ->where('type', 'asset')
            ->where('active', true)
            ->orderBy('code')
            ->pluck('name', 'code')
            ->map(fn ($name, $code) => "{$code} - {$name}")
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->badge()->searchable(),
                TextColumn::make('asset_account_code')->label('Asset account')->placeholder('From payment method')->searchable(),
                IconColumn::make('is_default')->label('Default')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('orders_count')->label('Orders')->counts('orders')->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allowsAny(
            RestaurantPermission::COLLECTION_POINTS_READ,
            RestaurantPermission::COLLECTION_POINTS_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::COLLECTION_POINTS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::COLLECTION_POINTS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::COLLECTION_POINTS_DELETE)
            && ! DB::table('restaurant_orders')->where('collection_point_id', $record->getKey())->exists();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->withCount('orders');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionPoints::route('/'),
            'create' => Pages\CreateCollectionPoint::route('/create'),
            'edit' => Pages\EditCollectionPoint::route('/{record}/edit'),
        ];
    }
}
