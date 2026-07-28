<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RecipeItem extends Model implements AuditableContract
{
    protected $table = 'restaurant_recipe_items';

    protected $fillable = [
        'restaurant_menu_item_id',
        'restaurant_stock_item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'restaurant_menu_item_id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'restaurant_stock_item_id');
    }

    use Auditable;
}
