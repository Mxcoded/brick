<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_order_id', 'restaurant_menu_item_id', 'quantity', 'instructions'];
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
