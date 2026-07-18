<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Item
 * Represents a general item in the catalog.
 */
class Item extends Model
{
    use HasFactory, HasProperty, SoftDeletes;

    protected $fillable = ['sku', 'supplier_id', 'description', 'category', 'price', 'unit_of_measurement', 'unit_value', 'photo_path', 'min_stock', 'max_stock', 'property_id'];

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

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function cycleCounts(): HasMany
    {
        return $this->hasMany(CycleCount::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(ItemConversion::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_stock')
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM store_items WHERE store_items.item_id = items.id) < items.min_stock');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ItemReturn::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public static function generateNextSku(): string
    {
        $last = self::where('sku', 'LIKE', 'BRK-%')
            ->orderByRaw('CAST(SUBSTRING(sku, 5) AS UNSIGNED) DESC')
            ->value('sku');

        if ($last) {
            $num = (int) substr($last, 4) + 1;
        } else {
            $num = 1;
        }

        return 'BRK-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
