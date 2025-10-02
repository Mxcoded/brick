<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Item
 * Represents a general item in the catalog.
 */
class Item extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'description', 'category', 'price', 'unit_of_measurement', 'unit_value'];

    /**
     * Get the supplier that owns the item.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the store items with this item.
     */
    public function storeItems(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }

    /**
     * Get the transfers associated with this item.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * Get the usage logs for this item.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }

    /**
     * Get the price history records for this item.
     */
    public function priceHistory(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
