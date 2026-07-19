<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class Payment extends Model implements AuditableInterface
{
    use Auditable, HasProperty, SoftDeletes;

    protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'restaurant_payments';

    protected $fillable = [
        'restaurant_order_id',
        'registration_id',
        'charge_type_id',
        'amount',
        'method',
        'reference',
        'change_due',
        'status',
        'paid_at',
        'notes',
        'property_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'change_due' => 'decimal:2',
            'paid_at' => 'datetime',
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
}
