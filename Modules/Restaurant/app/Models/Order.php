<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'reason',
        'tracking_status',
        'shift_id',
        'subtotal',
        'discount',
        'discount_type',
        'vat',
        'vat_rate',
        'grand_total',
    ];

    protected $table = 'restaurant_orders';

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'restaurant_order_id');
    }

    public function shift()
    {
        return $this->belongsTo(WaiterShift::class, 'shift_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'restaurant_order_id');
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
