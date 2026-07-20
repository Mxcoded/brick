<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Refund extends Model
{
    protected $fillable = [
        'gateway',
        'gateway_reference',
        'transaction_reference',
        'refundable_type',
        'refundable_id',
        'amount',
        'currency',
        'status',
        'reason',
        'metadata',
        'created_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function refundable(): MorphTo
    {
        return $this->morphTo();
    }
}
