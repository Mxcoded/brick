<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class OrderItem extends Model implements AuditableInterface
{
    use Auditable, HasFactory;

    protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = ['restaurant_order_id', 'restaurant_menu_item_id', 'quantity', 'instructions', 'split_group'];

    protected $table = 'restaurant_order_items';

    public function order()
    {
        return $this->belongsTo(Order::class, 'restaurant_order_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'restaurant_menu_item_id');
    }
}
