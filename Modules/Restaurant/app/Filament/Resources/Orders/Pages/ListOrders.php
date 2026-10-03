<?php

namespace Modules\Restaurant\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Filament\Resources\Orders\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'unsettled' => Tab::make('Awaiting payment')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotIn('status', ['completed', 'rejected', 'void'])
                    // Left side is the amount owed, right side the completed
                    // payments. Part-paid orders stay in this tab because the
                    // comparison is against the remaining balance, not merely
                    // the existence of a payment.
                    ->where('grand_total', '>', $this->paidSubquery())
                ),

            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tracking_status', 'paid')),

            'void' => Tab::make('Void')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'void')),
        ];
    }

    /**
     * Correlated sum of completed payments for the order being filtered.
     *
     * Built with the query builder rather than a raw string so the correlation
     * is compiled into `where grand_total > (select ...)`.
     */
    protected function paidSubquery(): QueryBuilder
    {
        return DB::table('restaurant_payments')
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('restaurant_payments.restaurant_order_id', 'restaurant_orders.id')
            ->where('status', 'completed');
    }

    protected function getHeaderActions(): array
    {
        // No create action: an order is only ever built by the POS or the online
        // checkout, which own the cart and the pricing rules.
        return [];
    }
}
