<?php

namespace Modules\Restaurant\Filament\Resources\SalesPoints;

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
use Modules\Restaurant\Models\SalesPoint;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Outlets inside the venue: a cafe, a restaurant floor, a kiosk.
 *
 * The revenue account field is the one that matters. Leave it empty and the
 * outlet is purely a label for the docket and the sales report, and money keeps
 * landing in the module revenue account exactly as it did before. Point it at a
 * code and every sale from this outlet credits that income account instead.
 */
class SalesPointResource extends Resource
{
    protected static ?string $model = SalesPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Sales Points';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'sales point';

    protected static ?string $pluralModelLabel = 'sales points';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/sales-points';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(191)
                ->placeholder('Cafe'),

            TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(50)
                // Printed on the docket and used in exports, so it is a short
                // token the property fixes rather than a display string.
                ->unique(ignoreRecord: true)
                ->helperText('Short identifier, e.g. CAFE or RST.'),

            Select::make('revenue_account_code')
                ->label('Revenue account')
                // The chart of accounts lives in Finance, but an outlet's
                // revenue account is the property manager's decision about this
                // module, so it is configured here rather than buried in
                // Finance's own screens.
                ->options(fn () => static::revenueAccountOptions())
                ->placeholder('Use the default restaurant revenue account')
                ->helperText('Leave empty to keep posting to the default restaurant revenue account (4100).'),

            Toggle::make('is_default')
                ->label('Default sales point')
                ->helperText('Used for guest orders placed online or from a room, which have no waiter shift to inherit an outlet from.')
                // Only one can be the default: the flag would be meaningless if
                // two rows claimed it and the winner depended on row order.
                ->afterStateUpdated(function ($state) {
                    if (! $state) {
                        return;
                    }

                    SalesPoint::query()->whereKeyNot($this->record?->getKey())->update(['is_default' => false]);
                }),

            Toggle::make('is_active')
                ->label('Active')
                ->helperText('Inactive outlets stay on historic orders but disappear from the waiter terminal.')
                ->default(true),

            Textarea::make('description')
                ->label('Description')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function revenueAccountOptions(): array
    {
        return ChartOfAccount::query()
            ->where('type', 'income')
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->badge()
                    ->searchable(),

                TextColumn::make('revenue_account_code')
                    ->label('Revenue account')
                    // Blank means the sale posts to the module default, which is
                    // a deliberate choice rather than missing information, so it
                    // is spelled out instead of left empty.
                    ->placeholder('Default (4100)')
                    ->searchable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->tooltip('Default for guest orders'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable(),
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
            RestaurantPermission::SALES_POINTS_READ,
            RestaurantPermission::SALES_POINTS_CREATE,
        );
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::SALES_POINTS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::SALES_POINTS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        // Deleting an outlet that has taken orders would strip historic revenue
        // of its attribution. The FK nulls the column instead of cascading, so
        // the sale survives, but only as an unattributed one.
        return RestaurantPermission::allows(RestaurantPermission::SALES_POINTS_DELETE)
            && ! DB::table('restaurant_orders')->where('sales_point_id', $record->getKey())->exists();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->withCount('orders');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesPoints::route('/'),
            'create' => Pages\CreateSalesPoint::route('/create'),
            'edit' => Pages\EditSalesPoint::route('/{record}/edit'),
        ];
    }
}
