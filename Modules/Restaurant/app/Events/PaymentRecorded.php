<?php

namespace Modules\Restaurant\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Restaurant\Models\Payment;

class PaymentRecorded
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
        public float $balanceRemaining,
    ) {}
}
