<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Inventory\Database\Factories\TransferFactory;

/**
 * Class Transfer
 * Represents a logged transfer of items between stores.
 */
class Transfer extends Model
{
    use HasFactory;

    protected $fillable = ['from_store_id', 'to_store_id', 'item_id', 'quantity', 'notes'];

    /**
     * Get the store from which the item was transferred.
     */
    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    /**
     * Get the store to which the item was transferred.
     */
    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    /**
     * Get the item that was transferred.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
