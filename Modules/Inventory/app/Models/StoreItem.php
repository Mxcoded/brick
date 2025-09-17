<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Inventory\Database\Factories\StoreItemFactory;

/**
 * Class StoreItem
 * Represents a specific item's stock at a given store and lot.
 */
class StoreItem extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'item_id', 'lot_number', 'quantity', 'total_cost', 'expiry_date'];

    /**
     * Get the store that owns the item stock.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the item associated with the stock.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
