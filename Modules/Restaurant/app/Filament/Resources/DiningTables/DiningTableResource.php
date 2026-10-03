<?php

namespace Modules\Restaurant\Filament\Resources\DiningTables;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// Aliased: this file needs both the Filament table schema and the model, and
// both are called Table.
use Modules\Restaurant\Models\Table as DiningTable;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

class DiningTableResource extends Resource
{
    protected static ?string $model = DiningTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Dining Tables';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'table';

    protected static ?string $pluralModelLabel = 'tables';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $slug = 'restaurant/tables';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->label('Table number')
                ->required()
                ->maxLength(191)
                // The legacy endpoint rejected duplicates on this column, and the
                // waiter terminal looks tables up by number when opening a bill.
                ->unique(ignoreRecord: true),

            TextInput::make('capacity')
                ->label('Seats')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(100)
                ->default(4),

            TextInput::make('section')
                ->label('Section')
                ->maxLength(191)
                ->placeholder('Main hall')
                // Used to filter the floor plan, so suggestions beat free typing.
                ->datalist(fn (): array => DiningTable::query()
                    ->whereNotNull('section')
                    ->distinct()
                    ->orderBy('section')
                    ->pluck('section')
                    ->all()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->columns([
                TextColumn::make('number')
                    ->label('Table')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('section')
                    ->badge()
                    ->placeholder('Unassigned')
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Seats')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
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
            RestaurantPermission::TABLES_READ,
            RestaurantPermission::TABLES_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::TABLES_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::TABLES_UPDATE);
    }

    public static function canDelete($record): bool
    {
        // Refusing to remove a table that has served orders keeps the order
        // history readable. The legacy delete route did not check this.
        return RestaurantPermission::allows(RestaurantPermission::TABLES_DELETE)
            && $record->orders()->doesntExist();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->withCount('orders');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiningTables::route('/'),
            'create' => Pages\CreateDiningTable::route('/create'),
            'edit' => Pages\EditDiningTable::route('/{record}/edit'),
        ];
    }
}
