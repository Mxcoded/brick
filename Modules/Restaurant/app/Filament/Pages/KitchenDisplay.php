<?php

namespace Modules\Restaurant\Filament\Pages;

use BackedEnum;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Services\KitchenService;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * The kitchen display, as a Filament page.
 *
 * The board is a polling screen rather than a table: kitchen staff scan columns
 * of order cards and tap one button, so a grid of cards with `wire:poll` is the
 * honest shape for it.
 *
 * The query is deliberately the same one the legacy `kdsData` endpoint uses --
 * `tracking_status` in (pending, preparing, ready), or a null tracking status on
 * a still-pending order. Reusing it rather than tidying it is the point: an
 * order that the old screen showed must appear on this one, or the kitchen
 * silently loses a table. The old screen keeps working and is not redirected.
 */
class KitchenDisplay extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Kitchen Display';

    // Above the menu screens: this is the screen that is open all service.
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'restaurant/kitchen';

    protected static ?string $title = 'Kitchen display';

    protected string $view = 'restaurant::filament.pages.kitchen-display';

    public static function canAccess(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::KITCHEN_READ);
    }

    public function canUpdate(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::KITCHEN_UPDATE);
    }

    /**
     * No "create" affordance on a board of live orders.
     */
    public function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Orders grouped into the three columns the kitchen works through.
     *
     * @return array<string, array{label: string, icon: string, orders: Collection<int, Order>}>
     */
    public function board(): array
    {
        $orders = Order::with('orderItems.menuItem.category')
            ->where(function ($query) {
                $query->whereIn('tracking_status', [
                    OrderStatus::PENDING,
                    OrderStatus::PREPARING,
                    OrderStatus::READY,
                ])->orWhere(function ($pending) {
                    $pending->whereNull('tracking_status')
                        ->where('status', OrderStatus::PENDING);
                });
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Order $order): string => $this->columnFor($order));

        $columns = [
            OrderStatus::PENDING => ['label' => 'Pending', 'icon' => 'heroicon-o-bell-alert'],
            OrderStatus::PREPARING => ['label' => 'Preparing', 'icon' => 'heroicon-o-fire'],
            OrderStatus::READY => ['label' => 'Ready to serve', 'icon' => 'heroicon-o-check-badge'],
        ];

        $board = [];

        foreach ($columns as $key => $meta) {
            $board[$key] = [
                ...$meta,
                'orders' => $orders->get($key, collect()),
            ];
        }

        return $board;
    }

    /**
     * Every live order id, so the view can spot a new arrival and beep.
     *
     * @return array<int, int>
     */
    public function orderIds(): array
    {
        return collect($this->board())
            ->flatMap(fn (array $column): array => $column['orders']->pluck('id')->all())
            ->values()
            ->all();
    }

    /**
     * Tickets still waiting to be accepted.
     *
     * The alarm rings for these and only these: an order the kitchen has
     * already picked up is their problem now, not a reason to keep the tone
     * going. Driving it from the count rather than from "an id I have not seen"
     * means the alarm also stops if the board is reopened mid-service.
     *
     * @return array<int, int>
     */
    public function pendingIds(): array
    {
        return $this->board()[OrderStatus::PENDING]['orders']
            ->pluck('id')
            ->values()
            ->all();
    }

    /**
     * The order total, for the card footer.
     */
    public function stationFor(?string $categoryName): string
    {
        $barCategories = config('restaurant.kitchen.bar_categories', ['Beverages']);

        return in_array($categoryName, $barCategories, true) ? 'Bar' : 'Kitchen';
    }

    public function accept(int $orderId): void
    {
        $this->advance($orderId, 'accept', 'Order accepted.');
    }

    public function markReady(int $orderId): void
    {
        $this->advance($orderId, 'markReady', 'Marked ready to serve.');
    }

    public function markServed(int $orderId): void
    {
        $this->advance($orderId, 'markServed', 'Marked served.');
    }

    /**
     * Refuse the move, tell the kitchen why, and let the poll redraw the card
     * in its new column.
     *
     * A rejection here is usually a stale board -- the waiter paid the bill a
     * second earlier -- so it is reported rather than thrown, because a
     * kitchen screen that throws stops polling and goes dark.
     */
    private function advance(int $orderId, string $method, string $success): void
    {
        if (! $this->canUpdate()) {
            Notification::make()->danger()->title('You cannot change kitchen tickets.')->send();

            return;
        }

        $order = Order::find($orderId);

        if (! $order instanceof Order) {
            Notification::make()->danger()->title('That order no longer exists.')->send();

            return;
        }

        try {
            app(KitchenService::class)->{$method}($order);
        } catch (DomainException $exception) {
            Notification::make()->danger()->title($exception->getMessage())->send();

            return;
        }

        Notification::make()->success()->title(sprintf('Order #%d — %s', $order->id, $success))->send();
    }

    /**
     * Which column a card belongs in.
     *
     * A still-pending order is the kitchen's to accept, whatever its tracking
     * status says. The final branch catches an accepted order whose tracking
     * status is still `pending`, which the query can return but which has no
     * column of its own -- without it that order would vanish from the board.
     */
    private function columnFor(Order $order): string
    {
        return match (true) {
            $order->status === OrderStatus::PENDING => OrderStatus::PENDING,
            $order->tracking_status === OrderStatus::PREPARING => OrderStatus::PREPARING,
            $order->tracking_status === OrderStatus::READY => OrderStatus::READY,
            default => OrderStatus::PENDING,
        };
    }
}
