<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OrderItem extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = ['restaurant_order_id', 'restaurant_menu_item_id', 'quantity', 'unit_price', 'instructions'];

    protected $table = 'restaurant_order_items';

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'restaurant_order_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'restaurant_menu_item_id');
    }

    /**
     * The price this line was actually sold at.
     *
     * Falls back to the live menu price only for rows written before the
     * snapshot existed, so a reprint of an old document still renders instead
     * of showing nothing.
     */
    public function resolvedUnitPrice(): float
    {
        if ($this->unit_price !== null) {
            return (float) $this->unit_price;
        }

        return (float) ($this->menuItem?->price ?? 0);
    }

    public function lineTotal(): float
    {
        return $this->resolvedUnitPrice() * (int) $this->quantity;
    }
}
