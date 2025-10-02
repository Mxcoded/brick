<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Inventory\Database\Factories\StoreFactory;

/**
 * Class Store
 * Represents a physical store or location in the inventory system.
 */
class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    /**
     * Get the store items associated with the store.
     */
    public function storeItems(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }

    /**
     * Get the transfers originating from this store.
     */
    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_store_id');
    }

    /**
     * Get the transfers arriving at this store.
     */
    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_store_id');
    }

    /**
     * Get the usage logs for items used at this store.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }
}
