<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RestockLog
 * Represents a log of an item's restocking event.
 */
class RestockLog extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'store_id', 'quantity', 'total_cost', 'lot_number', 'restocked_by'];

    /**
     * Get the item that was restocked.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the store where the item was restocked.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
