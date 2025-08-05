<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'restaurant_table_id',
        'type',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'status',
        'tracking_status',
    ];
    protected $table = 'restaurant_orders';

    public function table()
    {
        return $this->belongsTo(Table::class, 'restaurant_table_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'restaurant_order_id');
    }
    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
}
