<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StockTakeItem extends Model implements AuditableContract
{
    protected $fillable = ['stock_take_id', 'item_id', 'expected_quantity', 'actual_quantity', 'variance', 'notes'];

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    use Auditable;
}