<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\OrderItemFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['restaurant_order_id', 'restaurant_menu_item_id', 'quantity', 'instructions'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    // protected static function newFactory(): OrderItemFactory
    // {
    //     // return OrderItemFactory::new();
    // }
}
