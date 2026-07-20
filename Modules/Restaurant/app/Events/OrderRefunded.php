<?php

namespace Modules\Restaurant\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;

class OrderRefunded
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public Payment $payment,
        public string $reason,
    ) {}
}
