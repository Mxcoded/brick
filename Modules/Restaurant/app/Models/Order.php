<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Website\Models\Room;
// use Modules\Restaurant\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'source_id',
        'type',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'status',
        'reason', // New field for reason
        'tracking_status',
    ];

    protected $table = 'restaurant_orders';

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'restaurant_order_id');
    }

    public function getSourceAttribute()
    {
        if ($this->type === 'table') {
            return Table::find($this->source_id);
        } elseif ($this->type === 'room') {
            return Room::find($this->source_id);
        }
        return null;
    }
    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
}
