<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StoreLocation extends Model implements AuditableContract
{
    protected $fillable = ['store_id', 'zone', 'aisle', 'rack', 'shelf', 'code', 'notes'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storeItems(): HasMany
    {
        return $this->hasMany(StoreItem::class, 'location_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([$this->zone, $this->aisle, $this->rack, $this->shelf]);

        return implode(' / ', $parts) ?: ($this->code ?? 'Location #'.$this->id);
    }
    use Auditable;
}