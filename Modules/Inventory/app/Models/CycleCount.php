<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CycleCount extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'store_id', 'expected_quantity', 'actual_quantity', 'discrepancy', 'counted_by', 'counted_at', 'status', 'notes'];

    protected $casts = ['counted_at' => 'datetime'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
    use Auditable;
}