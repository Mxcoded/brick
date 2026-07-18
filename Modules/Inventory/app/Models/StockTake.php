<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTake extends Model
{
    use HasProperty;

    protected $fillable = ['store_id', 'taken_by', 'taken_at', 'completed_at', 'status', 'notes', 'property_id'];

    protected $casts = ['taken_at' => 'datetime', 'completed_at' => 'datetime'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function taker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }
}
