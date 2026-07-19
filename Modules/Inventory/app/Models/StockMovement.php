<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StockMovement extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'store_id', 'type', 'quantity_delta', 'cost_delta', 'reference_type', 'reference_id', 'user_id', 'notes'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(array $data): self
    {
        return static::create([
            'item_id' => $data['item_id'],
            'store_id' => $data['store_id'],
            'type' => $data['type'],
            'quantity_delta' => $data['quantity_delta'],
            'cost_delta' => $data['cost_delta'] ?? 0,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);
    }
    use Auditable;
}