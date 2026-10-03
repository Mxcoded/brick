<?php

namespace Modules\Restaurant\Support;

use Modules\Restaurant\Models\Order;

/**
 * The order lifecycle, in one place.
 *
 * The transitions were previously spelled out inline in the waiter controller,
 * the kitchen display and the admin order form, which is how a void ended up
 * reachable from more than one screen and how a rejected order could still be
 * accepted. The Filament table, the legacy routes and the kitchen display all
 * ask these methods instead.
 *
 * Note that `pending` -> `accepted` -> `void` is the only path to void, and it
 * deliberately does not cross a completed payment: that case needs a refund,
 * which is a Finance operation rather than a status change.
 */
class OrderStatus
{
    // Aliases, not redefinitions: the model constants are the source of truth
    // for the three statuses that predate this class.
    public const PENDING = Order::STATUS_PENDING;

    public const ACCEPTED = Order::STATUS_ACCEPTED;

    public const COMPLETED = Order::STATUS_COMPLETED;

    public const REJECTED = 'rejected';

    public const VOID = 'void';

    // Tracking (progress) values, which are independent of the order status.
    public const PREPARING = 'preparing';

    public const READY = 'ready';

    public const SERVED = 'served';

    public const PAID = 'paid';

    public const REFUNDED = 'refunded';

    public const CANCELLED = 'cancelled';

    public const DELIVERED = 'delivered';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
            self::VOID => 'Void',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function trackingOptions(): array
    {
        return [
            self::PENDING => 'Pending',
            self::PREPARING => 'Preparing',
            self::READY => 'Ready',
            self::SERVED => 'Served',
            self::DELIVERED => 'Delivered',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        ];
    }

    public static function color(?string $status): string
    {
        return match ($status) {
            self::COMPLETED => 'success',
            self::ACCEPTED => 'info',
            self::REJECTED, self::VOID => 'danger',
            self::PENDING => 'warning',
            default => 'gray',
        };
    }

    /**
     * Only a pending order can be accepted. Accepting one that has already been
     * accepted is what the kitchen display test asserts against, so the guard
     * lives with the status rather than in a view.
     */
    public static function canAccept(Order $order): bool
    {
        return $order->status === self::PENDING;
    }

    public static function canReject(Order $order): bool
    {
        return $order->status === self::PENDING;
    }

    /**
     * Kitchen progress: an order being cooked must have been accepted first, so
     * a card cannot skip straight from the queue to "ready" and out of the
     * kitchen without anyone having taken it.
     */
    public static function canMarkReady(Order $order): bool
    {
        return $order->status === self::ACCEPTED
            && $order->tracking_status === self::PREPARING;
    }

    /**
     * Served is the last step the kitchen owns. Once an order is paid the
     * tracking status becomes `paid`, so a settled bill can no longer be moved
     * along behind the waiter's back.
     */
    public static function canMarkServed(Order $order): bool
    {
        return $order->status === self::ACCEPTED
            && $order->tracking_status === self::READY;
    }

    /**
     * Only an active, unpaid order can be voided.
     */
    public static function canVoid(Order $order): bool
    {
        if ($order->status !== self::ACCEPTED) {
            return false;
        }

        return ! $order->payments()->where('status', 'completed')->exists();
    }

    public static function isPaid(Order $order): bool
    {
        return $order->status === self::COMPLETED && $order->tracking_status === self::PAID;
    }

    /**
     * Closed orders cannot be reopened or edited.
     */
    public static function isClosed(Order $order): bool
    {
        return in_array($order->status, [self::COMPLETED, self::REJECTED, self::VOID], true);
    }
}
