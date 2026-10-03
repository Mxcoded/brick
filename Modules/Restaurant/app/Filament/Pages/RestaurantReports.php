<?php

namespace Modules\Restaurant\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\SalesPoint;
use Modules\Restaurant\Models\WaiterShift;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Restaurant reporting, as read-only Filament pages.
 *
 * The aggregations here are the same ones the legacy salesReport(),
 * popularItems() and waiterPerformance() endpoints performed, kept in place so
 * the existing JSON consumers and Blade screens still agree with what an
 * administrator sees in the panel.
 *
 * Reading a summary and exporting the whole dataset are separate capabilities,
 * matching the route split: a CSV carries every row, the screen does not. The
 * export buttons are only rendered when the corresponding *.export permission
 * is held.
 */
class RestaurantReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'restaurant/reports';

    protected static ?string $title = 'Restaurant reports';

    protected string $view = 'restaurant::filament.pages.reports';

    /**
     * Form state, keyed by `statePath('data')` below.
     *
     * Public on purpose: Filament resolves the state path against the Livewire
     * component's public properties, so a protected `$data` throws when the form
     * is filled rather than quietly working.
     *
     * @var array<string, string>
     */
    public array $data = [
        'period' => 'today',
        'date_from' => null,
        'date_to' => null,
        'limit' => 20,
        'shift_id' => null,
    ];

    public static function canAccess(): bool
    {
        // Holding any single reporting capability is enough to see the reports
        // screen; the individual tabs below gate themselves.
        return RestaurantPermission::allowsAny(
            RestaurantPermission::REPORTS_SALES_READ,
            RestaurantPermission::REPORTS_POPULAR_ITEMS_READ,
            RestaurantPermission::REPORTS_WAITER_PERFORMANCE_READ,
            RestaurantPermission::REPORTS_SHIFT_READ,
        );
    }

    public function canViewSales(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_SALES_READ);
    }

    public function canExportSales(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_SALES_EXPORT);
    }

    public function canViewPopularItems(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_POPULAR_ITEMS_READ);
    }

    public function canExportPopularItems(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_POPULAR_ITEMS_EXPORT);
    }

    public function canViewWaiterPerformance(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_WAITER_PERFORMANCE_READ);
    }

    public function canViewShift(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::REPORTS_SHIFT_READ);
    }

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('period')
                    ->label('Period')
                    ->options([
                        'today' => 'Today',
                        'week' => 'This week',
                        'month' => 'This month',
                        'custom' => 'Custom range',
                    ])
                    ->default('today')
                    ->live()
                    ->required(),

                DatePicker::make('date_from')
                    ->label('From')
                    ->visible(fn (callable $get): bool => $get('period') === 'custom')
                    ->required(fn (callable $get): bool => $get('period') === 'custom'),

                DatePicker::make('date_to')
                    ->label('To')
                    ->visible(fn (callable $get): bool => $get('period') === 'custom')
                    ->required(fn (callable $get): bool => $get('period') === 'custom')
                    ->afterOrEqual('date_from'),

                TextInput::make('limit')
                    ->label('Top items')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->default(20)
                    ->helperText('How many rows the popular-items table shows.'),

                // Only rendered for a holder of reports.shift.read, so the
                // existence of shifts is not leaked to someone who may only
                // read the sales summary.
                Select::make('shift_id')
                    ->label('Shift')
                    ->helperText('Drill into one waiter shift to reconcile the till.')
                    ->options(fn (): array => $this->shiftOptions())
                    ->searchable()
                    ->visible(fn (): bool => $this->canViewShift()),
            ])
            ->statePath('data');
    }

    /**
     * The most recent shifts, for the shift picker.
     *
     * Bounded rather than complete: a picker listing every shift since the
     * hotel opened is unusable, and a manager reconciling cash is reconciling
     * today's or yesterday's drawer, not a shift from last quarter.
     *
     * @return array<int, string>
     */
    public function shiftOptions(): array
    {
        return WaiterShift::with('user')
            ->orderByDesc('clock_in')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (WaiterShift $shift): array => [
                $shift->id => sprintf(
                    '%s — %s%s',
                    $shift->user?->name ?? 'Unknown waiter',
                    $shift->clock_in?->format('j M Y, H:i') ?? 'no clock-in',
                    $shift->status ? ' · '.$shift->status : '',
                ),
            ])
            ->all();
    }

    /**
     * The reconciliation figures for the chosen shift.
     *
     * These are the same numbers the legacy `shiftReport` endpoint returned, in
     * the same shape, so the JSON consumer and this screen cannot disagree.
     *
     * Note that `expected_ending_cash` is opening cash plus the *total* of
     * completed orders, not plus the cash tenders only. That is what the legacy
     * endpoint did, and it is kept deliberately: a till figure that quietly
     * changed meaning under a manager mid-reconciliation would be worse than
     * the known simplification. Anyone relying on the discrepancy column
     * should read it against card takings for that shift.
     *
     * @return array<string, mixed>|null
     */
    public function selectedShift(): ?array
    {
        if (! $this->canViewShift()) {
            return null;
        }

        $shiftId = $this->form->getState()['shift_id'] ?? null;

        if (! $shiftId) {
            return null;
        }

        $shift = WaiterShift::with('user', 'orders.payments')->find($shiftId);

        if (! $shift instanceof WaiterShift) {
            return null;
        }

        $completedOrders = $shift->orders
            ->where('status', Order::STATUS_COMPLETED)
            ->values();

        $totalSales = (float) $completedOrders->sum('grand_total');
        $totalVat = (float) $completedOrders->sum('vat');
        $totalServiceCharge = (float) $completedOrders->sum('service_charge');
        $startingCash = (float) $shift->starting_cash;
        $expectedEndingCash = $startingCash + $totalSales;

        $endingCash = $shift->ending_cash !== null ? (float) $shift->ending_cash : null;

        return [
            'id' => $shift->id,
            'waiter' => $shift->user?->name ?? 'Unknown waiter',
            'clock_in' => $shift->clock_in,
            'clock_out' => $shift->clock_out,
            'starting_cash' => $startingCash,
            'ending_cash' => $endingCash,
            'expected_ending_cash' => $expectedEndingCash,
            'discrepancy' => $endingCash !== null ? $endingCash - $expectedEndingCash : null,
            // Gross, because this is the till reconciliation: the waiter counts
            // what went through the drawer, tax and service charge included.
            'total_sales' => $totalSales,
            'net_sales' => max(0, round($totalSales - $totalVat - $totalServiceCharge, 2)),
            'total_vat' => $totalVat,
            'total_service_charge' => $totalServiceCharge,
            'order_count' => $completedOrders->count(),
            'status' => $shift->status,
            'payment_methods' => Payment::whereIn('restaurant_order_id', $completedOrders->pluck('id'))
                ->completed()
                ->get()
                ->groupBy('method')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => (float) $items->sum('amount'),
                ]),
            'orders' => $completedOrders
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'type' => $order->type,
                    'customer' => $order->customer_name ?: 'Walk-in',
                    'total' => (float) $order->grand_total,
                    'paid_at' => $order->updated_at,
                ]),
        ];
    }

    protected function range(): array
    {
        $data = $this->form->getState();
        $now = now();

        [$from, $to] = match ($data['period'] ?? 'today') {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'custom' => [
                Carbon::parse($data['date_from'])->startOfDay(),
                Carbon::parse($data['date_to'])->endOfDay(),
            ],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };

        return [$from, $to];
    }

    /**
     * @return array<string, mixed>
     */
    public function salesSummary(): array
    {
        [$from, $to] = $this->range();

        $orders = Order::with('payments')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', Order::STATUS_COMPLETED)
            ->get();

        $totalSales = (float) $orders->sum('grand_total');
        $totalVat = (float) $orders->sum('vat');
        $totalServiceCharge = (float) $orders->sum('service_charge');
        $orderCount = $orders->count();

        $paymentMethods = Payment::whereIn('restaurant_order_id', $orders->pluck('id'))
            ->completed()
            ->get()
            ->groupBy('method')
            ->map(fn ($items) => [
                'count' => $items->count(),
                'total' => (float) $items->sum('amount'),
            ]);

        return [
            // Gross is what the customers paid. Net is what the restaurant
            // earned, and it is the figure the ledger credits to 4100, so
            // reporting only the gross overstates revenue by the VAT and the
            // service charge.
            'total_sales' => $totalSales,
            'net_sales' => max(0, round($totalSales - $totalVat - $totalServiceCharge, 2)),
            'total_discounts' => (float) $orders->sum('discount'),
            'total_vat' => $totalVat,
            'total_service_charge' => $totalServiceCharge,
            'order_count' => $orderCount,
            'average_order' => $orderCount > 0 ? $totalSales / $orderCount : 0.0,
            'from' => $from,
            'to' => $to,
            'payment_methods' => $paymentMethods,
            // Net revenue is what the ledger actually credited, and when the
            // outlets have revenue accounts of their own that split is the only
            // way to see how the restaurant did per counter.
            'sales_points' => $this->salesPointBreakdown($orders),
        ];
    }

    /**
     * Net revenue per outlet for the selected range.
     *
     * Orders with no sales point are reported under the module revenue account
     * rather than dropped: they were posted to 4100, so dropping them would
     * make the per-outlet figures disagree with the ledger.
     *
     * @param  Collection<int, Order>  $orders
     * @return Collection<string, array{gross: float, vat: float, service_charge: float, net: float, orders: int}>
     */
    protected function salesPointBreakdown(Collection $orders): Collection
    {
        $names = SalesPoint::query()
            ->pluck('name', 'id')
            ->all();

        return $orders
            ->groupBy(fn (Order $order) => $order->sales_point_id ?: 0)
            ->map(function (Collection $group, $id) use ($names) {
                $gross = (float) $group->sum('grand_total');
                $vat = (float) $group->sum('vat');
                $serviceCharge = (float) $group->sum('service_charge');

                return [
                    'name' => $id ? ($names[$id] ?? 'Deleted sales point') : 'Unassigned',
                    'gross' => $gross,
                    'vat' => $vat,
                    'service_charge' => $serviceCharge,
                    'net' => max(0, round($gross - $vat - $serviceCharge, 2)),
                    'orders' => $group->count(),
                ];
            })
            ->sortByDesc('gross')
            ->values();
    }

    /**
     * @return Collection<int, object>
     */
    public function popularItems()
    {
        [$from, $to] = $this->range();
        $limit = (int) ($this->form->getState()['limit'] ?? 20);

        return OrderItem::selectRaw('restaurant_menu_item_id, SUM(quantity) as total_qty, SUM(quantity * mi.price) as total_revenue')
            ->join('restaurant_menu_items as mi', 'mi.id', '=', 'restaurant_order_items.restaurant_menu_item_id')
            ->join('restaurant_orders as o', 'o.id', '=', 'restaurant_order_items.restaurant_order_id')
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.status', Order::STATUS_COMPLETED)
            ->groupBy('restaurant_menu_item_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($i) => [
                'name' => $i->menuItem?->name ?? 'Deleted',
                'category' => $i->menuItem?->category?->name ?? '',
                'quantity' => (int) $i->total_qty,
                'revenue' => (float) $i->total_revenue,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function waiterPerformance()
    {
        [$from, $to] = $this->range();

        return WaiterShift::with('user')
            ->withCount(['orders' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)])
            ->withSum(['orders' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)], 'grand_total')
            ->whereBetween('clock_in', [$from, $to])
            ->get()
            ->groupBy('user_id')
            ->map(fn ($items, $userId) => [
                'waiter_name' => $items->first()->user?->name ?? 'Deleted',
                'shifts' => $items->count(),
                'orders_taken' => $items->sum('orders_count'),
                'total_sales' => (float) $items->sum('orders_sum_grand_total'),
                'hours_worked' => (float) $items->filter(fn ($s) => $s->clock_out)
                    ->sum(fn ($s) => round($s->clock_out->diffInHours($s->clock_in), 1)),
            ])
            ->values();
    }

    /**
     * Re-read the filter form and refresh the report queries.
     *
     * Kept as an explicit action rather than `->live()` on every field so a
     * half-typed custom date range does not fire three broken queries.
     */
    public function applyFilters(): void
    {
        $this->form->validate();

        $this->dispatch('$refresh');
    }

    /**
     * The current filter as a query string, for the legacy CSV export routes.
     *
     * Those endpoints validate the same `period`/`date_from`/`date_to` keys, so
     * an export is always the report on screen rather than a separately chosen
     * range that could disagree with it.
     *
     * @return array<string, string>
     */
    public function exportQuery(): array
    {
        $data = $this->form->getState();

        return array_filter([
            'period' => $data['period'] ?? 'today',
            'date_from' => $data['period'] === 'custom' ? ($data['date_from'] ?? null) : null,
            'date_to' => $data['period'] === 'custom' ? ($data['date_to'] ?? null) : null,
        ], fn ($value): bool => $value !== null);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
