<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ItemConversion extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'from_unit', 'to_unit', 'conversion_rate'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    use Auditable;
}
