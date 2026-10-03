<?php

namespace Modules\Restaurant\Services;

use DomainException;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Support\OrderStatus;

/**
 * Kitchen progress, in one place.
 *
 * The legacy `kdsAcceptOrder` endpoint set `status` and `tracking_status`
 * inline and left the "ready" and "served" steps to the waiter's status form,
 * which is why the kitchen display could render a "ready" column that nothing
 * on that screen could put an order into. The Filament kitchen board drives the
 * same three steps, so they live here and the guards stay in OrderStatus.
 *
 * The legacy endpoint is deliberately left as it is: it is covered by
 * KitchenFlowTest and by any browser tab still open on the old screen. Both
 * paths now produce identical rows for identical input.
 */
class KitchenService
{
    public function accept(Order $order): void
    {
        $this->guard($order, OrderStatus::canAccept($order), 'Only a pending order can be accepted.');

        $order->status = OrderStatus::ACCEPTED;
        $order->tracking_status = OrderStatus::PREPARING;
        $order->save();
    }

    public function markReady(Order $order): void
    {
        $this->guard($order, OrderStatus::canMarkReady($order), 'Only an order being prepared can be marked ready.');

        $order->tracking_status = OrderStatus::READY;
        $order->save();
    }

    public function markServed(Order $order): void
    {
        $this->guard($order, OrderStatus::canMarkServed($order), 'Only a ready order can be marked served.');

        $order->tracking_status = OrderStatus::SERVED;
        $order->save();
    }

    /**
     * @throws DomainException
     */
    private function guard(Order $order, bool $allowed, string $message): void
    {
        if (! $allowed) {
            throw new DomainException($message);
        }
    }
}
