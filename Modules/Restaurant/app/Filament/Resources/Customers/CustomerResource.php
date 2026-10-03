<?php

namespace Modules\Restaurant\Filament\Resources\Customers;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurant\Filament\Resources\Customers\Pages\CustomerStats;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Guest records and the loyalty counters that ride on them.
 *
 * `visit_count`, `total_spent` and `loyalty_points` are written by
 * OrderPaymentService when an order settles, so they are shown read-only here.
 * Editing them by hand would put the customer record out of step with the order
 * history it is derived from.
 *
 * There is no delete. The taxonomy has customers.read/create/update but no
 * customers.delete, because a guest who has eaten here still has an order
 * history that Finance and the reports reference.
 */
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'customer';

    protected static ?string $pluralModelLabel = 'customers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'restaurant/customers';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->label('Phone')
                ->tel()
                ->maxLength(20)
                ->unique(ignoreRecord: true)
                // Phone is the loyalty key: OrderPaymentService finds the guest
                // by it and increments the counters there, so a duplicate or
                // missing number silently breaks the history.
                ->helperText('Used to match orders to this guest and to track loyalty points.'),

            TextInput::make('email')
                ->email()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Textarea::make('notes')
                ->rows(3)
                ->maxLength(1000),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')->columnSpan(1),
                TextEntry::make('phone')->label('Phone')->placeholder('—'),
                TextEntry::make('email')->label('Email')->placeholder('—'),

                TextEntry::make('visit_count')
                    ->label('Visits')
                    ->numeric(),

                TextEntry::make('total_spent')
                    ->label('Lifetime spend')
                    ->money('NGN'),

                TextEntry::make('loyalty_points')
                    ->label('Loyalty points')
                    ->badge()
                    ->helperText('One point per 100 spent.'),

                TextEntry::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('total_spent', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('visit_count')
                    ->label('Visits')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_spent')
                    ->label('Lifetime spend')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('loyalty_points')
                    ->label('Points')
                    ->badge()
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('First seen')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('has_orders')
                    ->label('Has orders only')
                    ->query(fn (Builder $query): Builder => $query->has('orders')),
            ])
            ->actions([
                Action::make('stats')
                    ->label('History')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Customer $record): string => CustomerStats::getUrl(['record' => $record])),

                EditAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::CUSTOMERS_READ);
    }

    public static function canCreate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::CUSTOMERS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::CUSTOMERS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        // Guest history outlives the record; the taxonomy offers no delete for
        // that reason.
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->withTrashed()->withCount('orders');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'stats' => CustomerStats::route('/{record}/stats'),
        ];
    }
}
