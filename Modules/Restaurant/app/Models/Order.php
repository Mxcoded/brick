<?php

namespace Modules\Restaurant\Models;

use App\Models\Room;
use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Restaurant\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory, HasProperty;

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
        'property_id',
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
        } elseif ($this->type === 'room' && class_exists(Room::class)) {
            return Room::find($this->source_id);
        }

        return null;
    }

    public function getAmountDueAttribute(): float
    {
        $paid = $this->payments()->where('status', 'completed')->sum('amount');

        return max(0, (float) $this->grand_total - (float) $paid);
    }
    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
}
