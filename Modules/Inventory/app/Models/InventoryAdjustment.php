<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InventoryAdjustment extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'store_id', 'type', 'quantity_change', 'reason', 'adjusted_by'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    use Auditable;
}
