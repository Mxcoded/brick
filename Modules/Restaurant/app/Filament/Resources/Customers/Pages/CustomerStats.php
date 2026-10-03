<?php

namespace Modules\Restaurant\Filament\Resources\Customers\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurant\Filament\Resources\Customers\CustomerResource;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\Order;

/**
 * One guest's order history.
 *
 * Replaces the legacy customer_show view: the customer summary on top, their
 * orders below, scoped by phone rather than by a customer foreign key, because
 * that is how orders are actually linked (Order.customer_phone).
 */
class CustomerStats extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Customer history';

    protected static bool $shouldRegisterNavigation = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to customers')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    protected function contentSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->hiddenLabel()
                    ->state(fn (): string => $this->getRecord()->name),

                TextEntry::make('phone')
                    ->label('Phone')
                    ->state(fn (): string => $this->getRecord()->phone ?: '—'),

                TextEntry::make('visit_count')
                    ->label('Visits')
                    ->state(fn (): string => (string) $this->getRecord()->visit_count),

                TextEntry::make('total_spent')
                    ->label('Lifetime spend')
                    ->state(fn (): string => 'NGN '.number_format((float) $this->getRecord()->total_spent, 2)),

                TextEntry::make('loyalty_points')
                    ->label('Points')
                    ->badge()
                    ->state(fn (): string => (string) $this->getRecord()->loyalty_points),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Orders')
            ->query(fn (): Builder => Order::query()
                ->where('customer_phone', $this->getRecord()->phone)
                ->with('orderItems')
                ->latest())
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->formatStateUsing(fn (int $state): string => '#'.$state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('order_items_count')
                    ->label('Items')
                    ->counts('orderItems'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function getRecord(): Customer
    {
        return $this->record;
    }
}
