<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasProperty;

    protected $fillable = ['item_id', 'store_id', 'type', 'severity', 'message', 'sent', 'sent_at', 'resolved', 'resolved_at', 'property_id'];

    protected function casts(): array
    {
        return [
            'sent' => 'boolean',
            'sent_at' => 'datetime',
            'resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
