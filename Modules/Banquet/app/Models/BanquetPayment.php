<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'banquet_order_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(BanquetOrder::class, 'banquet_order_id');
    }
}
