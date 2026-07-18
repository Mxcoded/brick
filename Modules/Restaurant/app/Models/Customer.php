<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasProperty, SoftDeletes;

    protected $table = 'restaurant_customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'visit_count',
        'total_spent',
        'loyalty_points',
        'notes',
        'property_id',
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
