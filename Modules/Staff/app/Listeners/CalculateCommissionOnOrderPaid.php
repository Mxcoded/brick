<?php

namespace Modules\Staff\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Events\OrderPaid;

class CalculateCommissionOnOrderPaid implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        Log::info('Staff commission calculated for paid order', [
            'order_id' => $event->order->id,
            'total_amount' => $event->totalAmount,
            'property_id' => $event->propertyId,
        ]);
    }
}
