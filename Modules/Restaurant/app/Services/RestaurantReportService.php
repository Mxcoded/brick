<?php

namespace Modules\Restaurant\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\WaiterShift;

class RestaurantReportService
{
    public function salesReport(Carbon $from, Carbon $to): array
    {
        $summaryRow = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed')
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as total_sales,
                COALESCE(SUM(discount), 0) as total_discounts,
                COALESCE(SUM(vat), 0) as total_vat,
                COUNT(*) as order_count
            ')
            ->first();

        $totalSales = (float) $summaryRow->total_sales;
        $totalDiscounts = (float) $summaryRow->total_discounts;
        $totalVat = (float) $summaryRow->total_vat;
        $orderCount = (int) $summaryRow->order_count;
        $averageOrder = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $paymentMethods = Payment::whereHas('order', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to])->where('status', 'completed');
        })->completed()
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        $hourly = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as `count`, SUM(grand_total) as `total`')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn ($row) => [
                'hour' => str_pad($row->hour, 2, '0', STR_PAD_LEFT).':00',
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);

        return [
            'summary' => [
                'total_sales' => $totalSales,
                'total_discounts' => $totalDiscounts,
                'total_vat' => $totalVat,
                'order_count' => $orderCount,
                'average_order' => $averageOrder,
                'period' => ['from' => $from, 'to' => $to],
            ],
            'payment_methods' => $paymentMethods,
            'hourly' => $hourly,
        ];
    }

    public function popularItems(Carbon $from, Carbon $to, int $limit = 20): Collection
    {
        return OrderItem::selectRaw('restaurant_menu_item_id, SUM(quantity) as total_qty, SUM(quantity * mi.price) as total_revenue')
            ->join('restaurant_menu_items as mi', 'mi.id', '=', 'restaurant_order_items.restaurant_menu_item_id')
            ->join('restaurant_orders as o', 'o.id', '=', 'restaurant_order_items.restaurant_order_id')
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.status', 'completed')
            ->groupBy('restaurant_menu_item_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->load('menuItem.category');
    }

    public function waiterPerformance(Carbon $from, Carbon $to): Collection
    {
        $waiters = WaiterShift::with('user')
            ->withCount(['orders' => fn ($q) => $q->where('status', 'completed')])
            ->withSum(['orders' => fn ($q) => $q->where('status', 'completed')], 'grand_total')
            ->whereBetween('clock_in', [$from, $to])
            ->get()
            ->map(fn ($s) => [
                'waiter_id' => $s->user_id,
                'waiter_name' => $s->user?->name ?? 'Deleted',
                'shifts' => 1,
                'orders_taken' => $s->orders_count,
                'total_sales' => (float) $s->orders_sum_grand_total,
                'hours_worked' => $s->clock_out
                    ? round($s->clock_out->diffInHours($s->clock_in), 1)
                    : null,
            ]);

        return $waiters->groupBy('waiter_id')->map(fn ($items) => [
            'waiter_name' => $items->first()['waiter_name'],
            'shifts' => $items->sum('shifts'),
            'orders_taken' => $items->sum('orders_taken'),
            'total_sales' => $items->sum('total_sales'),
            'hours_worked' => $items->sum('hours_worked'),
        ])->values();
    }

    public function shiftReport(int $shiftId): array
    {
        $shift = WaiterShift::with('user')->findOrFail($shiftId);

        $completedOrderIds = Order::where('shift_id', $shift->id)
            ->where('status', 'completed')
            ->pluck('id');

        $totalSales = (float) Order::whereIn('id', $completedOrderIds)->sum('grand_total');

        $paymentMethods = Payment::whereIn('restaurant_order_id', $completedOrderIds)
            ->completed()
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        $orderCount = $completedOrderIds->count();
        $expectedEndingCash = $shift->starting_cash + $totalSales;
        $discrepancy = $shift->ending_cash ? $shift->ending_cash - $expectedEndingCash : null;

        return [
            'shift' => [
                'id' => $shift->id,
                'waiter' => $shift->user?->name ?? 'N/A',
                'clock_in' => $shift->clock_in,
                'clock_out' => $shift->clock_out,
                'starting_cash' => $shift->starting_cash,
                'ending_cash' => $shift->ending_cash,
                'expected_ending_cash' => $expectedEndingCash,
                'discrepancy' => $discrepancy,
                'total_sales' => $totalSales,
                'order_count' => $orderCount,
                'status' => $shift->status,
            ],
            'payment_methods' => $paymentMethods,
            'orders' => Order::whereIn('id', $completedOrderIds)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'type' => $o->type,
                    'customer' => $o->customer_name ?? 'Walk-in',
                    'total' => $o->grand_total,
                    'paid_at' => $o->updated_at,
                ]),
        ];
    }
}
