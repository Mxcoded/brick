<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Models\Room;
// use Modules\Restaurant\Database\Factories\OrderFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Order extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'source_id',
        'sales_point_id',
        'collection_point_id',
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
        'service_charge',
        'service_charge_rate',
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

    /**
     * The outlet the sale was made in. Snapshotted on the order rather than
     * derived from the shift, so the order keeps its attribution after the
     * shift is closed and the waiter has moved to another counter.
     */
    public function salesPoint()
    {
        return $this->belongsTo(SalesPoint::class, 'sales_point_id');
    }

    /**
     * The physical terminal the money came in through.
     */
    public function collectionPoint()
    {
        return $this->belongsTo(CollectionPoint::class, 'collection_point_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'restaurant_order_id');
    }

    public function getBalanceAttribute(): float
    {
        $paid = $this->payments()
            ->whereIn('status', ['completed'])
            ->sum('amount');

        return max(0, (float) $this->grand_total - (float) $paid);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * What the sale actually earned, with VAT and any service charge taken out.
     *
     * This is the figure that belongs in 4100 Restaurant Revenue; grand_total is
     * what the customer handed over, which is not the same number.
     */
    public function getNetTotalAttribute(): float
    {
        return max(0, round(
            (float) $this->grand_total - (float) $this->vat - (float) $this->service_charge,
            2
        ));
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
