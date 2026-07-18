<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasProperty;

    protected $table = 'restaurant_stock_movements';

    protected $fillable = [
        'restaurant_stock_item_id',
        'property_id',
        'type',
        'quantity',
        'unit_cost',
        'reference',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'restaurant_stock_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
