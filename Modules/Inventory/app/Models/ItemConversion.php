<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemConversion extends Model
{
    protected $fillable = ['item_id', 'from_unit', 'to_unit', 'conversion_rate'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
