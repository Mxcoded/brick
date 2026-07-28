<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Inventory\Database\Factories\UsageLogFactory;

/**
 * Class UsageLog
 * Represents a log of an item's usage for maintenance.
 */
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UsageLog extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = ['item_id', 'store_id', 'department_id', 'quantity_used', 'unit_cost', 'used_for', 'technician_name', 'date_used', 'reference'];

    protected function casts(): array
    {
        return [
            'date_used' => 'date',
        ];
    }

    /**
     * Get the item that was used.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the store where the item was used.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the department where the item was used.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
