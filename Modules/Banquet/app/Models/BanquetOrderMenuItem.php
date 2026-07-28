<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class BanquetOrderMenuItem extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'banquet_order_day_id',
        'meal_type',
        'menu_items',
        'quantity',
        'unit_price',
        'total_price',
        'dietary_restrictions',
    ];

    protected $casts = [
        'menu_items' => 'array',
        'dietary_restrictions' => 'array',
    ];

    public function banquetOrderDay()
    {
        return $this->belongsTo(BanquetOrderDay::class);
    }
}
