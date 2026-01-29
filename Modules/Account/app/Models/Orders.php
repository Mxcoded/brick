<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Account\Database\Factories\OrdersFactory;

class Orders extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): OrdersFactory
    // {
    //     // return OrdersFactory::new();
    // }

    public function items() {
        return $this->hasMany(OrderItems::class);
    }

    public function invoice() {
        return $this->hasOne(Invoices::class);
    }

}
