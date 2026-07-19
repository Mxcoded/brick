<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Customer extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'restaurant_customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'visit_count',
        'total_spent',
        'loyalty_points',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_count' => 'integer',
            'total_spent' => 'decimal:2',
            'loyalty_points' => 'integer',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_phone', 'phone');
    }
}