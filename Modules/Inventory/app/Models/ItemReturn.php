<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ItemReturn extends Model implements AuditableContract
{
    protected $fillable = ['item_id', 'store_id', 'department_id', 'quantity_returned', 'reason', 'returned_by', 'received_by', 'reference', 'notes'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    use Auditable;
}
