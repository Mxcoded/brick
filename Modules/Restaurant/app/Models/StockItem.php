<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use HasProperty, SoftDeletes;

    protected $table = 'restaurant_stock_items';

    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'min_stock_level',
        'unit_cost',
        'description',
        'property_id',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'decimal:3',
            'min_stock_level' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class, 'restaurant_stock_item_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'restaurant_stock_item_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }
}
