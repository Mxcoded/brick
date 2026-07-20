<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Payment extends Model implements AuditableContract
{
    protected $table = 'restaurant_payments';

    protected $fillable = [
        'restaurant_order_id',
        'amount',
        'method',
        'reference',
        'change_due',
        'status',
        'paid_at',
        'notes',
        'finance_posted',
        'refunded_at',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'change_due' => 'decimal:2',
            'paid_at' => 'datetime',
            'finance_posted' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'restaurant_order_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    use Auditable;
}