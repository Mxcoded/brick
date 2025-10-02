<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PriceHistory
 * Represents a historical price record for an item from a specific supplier.
 */
class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_history';

    protected $fillable = ['item_id', 'supplier_id', 'price', 'effective_date'];

    /**
     * Get the item associated with the price history record.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the supplier associated with the price history record.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
