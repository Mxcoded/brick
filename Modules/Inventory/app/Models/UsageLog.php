<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Inventory\Database\Factories\UsageLogFactory;

/**
 * Class UsageLog
 * Represents a log of an item's usage for maintenance.
 */
class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'store_id', 'quantity_used', 'used_for', 'technician_name'];

    /**
     * Get the item that was used.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the store where the item was used.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
