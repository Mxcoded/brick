<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = ['restaurant_table_id', 'status'];

    public function table()
    {
        return $this->belongsTo(Table::class, 'restaurant_table_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
}
