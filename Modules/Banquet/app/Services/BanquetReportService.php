<?php

namespace Modules\Banquet\Services;

use Carbon\Carbon;
use Modules\Banquet\Models\BanquetOrder;

class BanquetReportService
{
    public function getReportData($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $orders = BanquetOrder::with(['customer', 'eventDays.menuItems'])
            ->whereIn('status', ['Completed', 'Cancelled', 'Confirmed'])
            ->whereHas('eventDays', function ($query) use ($start, $end) {
                $query->whereBetween('event_date', [$start, $end]);
            })
            ->get();

        $reportData = [];
        $statusCounts = ['Confirmed' => 0, 'Cancelled' => 0, 'Completed' => 0, 'Pending' => 0];
        $locationCounts = [];

        $totals = ['revenue' => 0, 'expenses' => 0, 'profit' => 0];

        foreach ($orders as $order) {
            if ($order->eventDays->isEmpty()) {
                $locationString = 'N/A';
                $guestCount = 0;
                $hallRentalFee = 0;
                $foodBeverageTotal = 0;
                $eventDateRange = $order->preparation_date->format('M d, Y');
            } else {
                $dates = $order->eventDays->sortBy('event_date');
                $eventDateRange = $dates->first()->event_date->format('M d').' - '.$dates->last()->event_date->format('M d, Y');

                $locationString = $order->eventDays->pluck('room')->unique()->implode(', ');
                $order->eventDays->pluck('room')->each(function ($room) use (&$locationCounts) {
                    $locationCounts[$room] = ($locationCounts[$room] ?? 0) + 1;
                });

                $guestCount = $order->eventDays->sum('guest_count');
                $hallRentalFee = $order->hall_rental_fees;
                $foodBeverageTotal = $order->eventDays->flatMap->menuItems->sum('total_price') ?? 0;
            }

            $orderTotal = $hallRentalFee + $foodBeverageTotal;
            $orderExpenses = $order->expenses;
            $orderProfit = $orderTotal - $orderExpenses;

            $totals['revenue'] += $orderTotal;
            $totals['expenses'] += $orderExpenses;
            $totals['profit'] += $orderProfit;

            if (isset($statusCounts[$order->status])) {
                $statusCounts[$order->status]++;
            }

            $reportData[] = [
                'order_id' => $order->order_id,
                'organization' => $order->customer->organization ?? $order->contact_person_name,
                'guest_count' => $guestCount,
                'event_date_range' => $eventDateRange,
                'location' => $locationString,
                'hall_rental_fees' => $hallRentalFee,
                'food_beverage_total' => $foodBeverageTotal,
                'total_revenue' => $orderTotal,
                'expenses' => $orderExpenses,
                'profit' => $orderProfit,
                'status' => $order->status,
            ];
        }

        $mostUsedLocation = ! empty($locationCounts) ? array_search(max($locationCounts), $locationCounts) : 'N/A';

        $summary = [
            'total_events' => $orders->count(),
            'confirmed' => $statusCounts['Confirmed'] + $statusCounts['Completed'],
            'cancelled' => $statusCounts['Cancelled'],
            'most_used_location' => $mostUsedLocation,
        ];

        return compact('reportData', 'summary', 'totals', 'startDate', 'endDate');
    }

    public function calculateProfitMargin($totalRevenue, $expenses): ?float
    {
        if ($totalRevenue <= 0) {
            return null;
        }
        $profit = $totalRevenue - ($expenses ?? 0);

        return ($profit / $totalRevenue) * 100;
    }
}
