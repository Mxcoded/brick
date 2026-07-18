<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleCount extends Model
{
    use HasProperty;

    protected $fillable = ['item_id', 'store_id', 'expected_quantity', 'actual_quantity', 'discrepancy', 'counted_by', 'counted_at', 'status', 'notes', 'property_id'];

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
}
