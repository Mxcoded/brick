<?php

namespace Modules\Banquet\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetPayment extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'property_id',
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
