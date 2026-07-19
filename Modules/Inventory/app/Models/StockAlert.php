<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StockAlert extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'store_id', 'type', 'severity', 'message', 'sent', 'sent_at', 'resolved', 'resolved_at'];

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
    use Auditable;
}