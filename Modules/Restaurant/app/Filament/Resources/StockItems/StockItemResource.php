<?php

namespace Modules\Restaurant\Filament\Resources\StockItems;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurant\Filament\Resources\StockItems\RelationManagers\MovementsRelationManager;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Restaurant stock and the recipes that consume it.
 *
 * The one thing this screen deliberately does not offer is a free-text edit of
 * the on-hand quantity. `stock_quantity` is the running total of the movement
 * ledger, so editing it directly would leave the history disagreeing with the
 * balance. Stock changes go through the Movements relation manager, which
 * appends a typed movement the way the old stockMovementStore endpoint did.
 */
class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Stock';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'stock item';

    protected static ?string $pluralModelLabel = 'stock items';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/stock';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(191),

            Select::make('unit')
                ->label('Unit')
                ->options([
                    'kg' => 'kg',
                    'g' => 'g',
                    'litre' => 'Litre',
                    'ml' => 'mL',
                    'piece' => 'Piece',
                    'packet' => 'Packet',
                    'carton' => 'Carton',
                    'bunch' => 'Bunch',
                ])
                ->required()
                ->default('kg'),

            TextInput::make('stock_quantity')
                ->label('Quantity in stock')
                ->numeric()
                ->required()
                ->minValue(0)
                ->step(0.001)
                ->default(0)
                // Only on creation: afterwards the balance belongs to the
                // movement ledger, and the helper says so rather than silently
                // letting a saved edit diverge from the history.
                ->helperText('Opening balance. After this, record changes as movements.')
                ->dehydrated(fn (?StockItem $record): bool => $record === null),

            TextInput::make('min_stock_level')
                ->label('Reorder level')
                ->numeric()
                ->required()
                ->minValue(0)
                ->step(0.001)
                ->default(0)
                ->helperText('Flags the item as low once stock falls to this level.'),

            TextInput::make('unit_cost')
                ->label('Unit cost')
                ->numeric()
                ->prefix('NGN')
                ->minValue(0)
                ->step(0.01),

            Textarea::make('description')
                ->rows(2)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->badge(),

                TextColumn::make('stock_quantity')
                    ->label('In stock')
                    ->numeric(3)
                    ->sortable()
                    // The single most useful thing on this screen.
                    ->color(fn (StockItem $record): string => $record->isLowStock() ? 'danger' : 'default')
                    ->description(fn (StockItem $record): ?string => $record->isLowStock()
                        ? 'At or below reorder level'
                        : null),

                TextColumn::make('min_stock_level')
                    ->label('Reorder at')
                    ->numeric(3)
                    ->toggleable(),

                TextColumn::make('unit_cost')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('recipe_items_count')
                    ->label('Used in')
                    ->counts('recipeItems')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('movements_count')
                    ->label('Movements')
                    ->counts('movements')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('deleted_at')
                    ->label('Trashed')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Trashed' : 'Active')
                    ->colors(['danger'])
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low stock only')
                    ->query(fn (Builder $query): Builder => $query->whereColumn(
                        'stock_quantity',
                        '<=',
                        'min_stock_level'
                    )),

                TernaryFilter::make('deleted_at')
                    ->label('Trashed only')
                    ->trueLabel('Trashed')
                    ->falseLabel('Active')
                    ->nullable(),
            ])->actions([
                EditAction::make(),
                RestoreAction::make()->visible(fn (): bool => RestaurantPermission::allows(
                    RestaurantPermission::STOCK_RESTORE
                )),
                DeleteAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allowsAny(
            RestaurantPermission::STOCK_READ,
            RestaurantPermission::STOCK_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::STOCK_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::STOCK_UPDATE);
    }

    public static function canDelete($record): bool
    {
        // An ingredient already used by a dish would leave recipe rows pointing
        // at a trashed item, so that has to be cleared first.
        return RestaurantPermission::allows(RestaurantPermission::STOCK_DELETE)
            && $record->recipeItems()->doesntExist();
    }

    public static function canRestore($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::STOCK_RESTORE);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->withTrashed()
            ->withCount(['recipeItems', 'movements']);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockItems::route('/'),
            'create' => Pages\CreateStockItem::route('/create'),
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }
}
