<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Events\OrderPaid;

class RecordRevenueOnOrderPaid implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        Log::info('Revenue recorded for paid order', [
            'order_id' => $event->order->id,
            'total_amount' => $event->totalAmount,
            'property_id' => $event->propertyId,
        ]);
    }
}
