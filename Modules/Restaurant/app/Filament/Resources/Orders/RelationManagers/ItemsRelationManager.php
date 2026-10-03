<?php

namespace Modules\Restaurant\Filament\Resources\Orders\RelationManagers;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Support\RestaurantPermission;

/**
 * The lines on a closed order, shown as a read-only breakdown.
 *
 * There is deliberately no add, edit or delete here. Order lines are priced by
 * the POS and the online checkout using the menu price and the VAT/discount
 * rules of the day; editing a line afterwards would change what the guest was
 * charged without any record of the correction. Voiding and re-ordering is the
 * auditable route, and both are available on the order itself.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Items';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('menuItem.name')
                    ->label('Item')
                    ->searchable()
                    ->placeholder('Item deleted from menu'),

                TextColumn::make('price')
                    ->label('Unit price')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Line total')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Kitchen note')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('id')
            ->paginated(false);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order')
                    ->hiddenLabel()
                    ->state(fn (Order $record): string => 'Order #'.$record->id.' · '.number_format((float) $record->grand_total, 2)),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::ORDERS_READ);
    }
}
