<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemReturn extends Model
{
    use HasProperty;

    protected $fillable = ['item_id', 'store_id', 'department_id', 'quantity_returned', 'reason', 'returned_by', 'received_by', 'reference', 'notes', 'property_id'];

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
}
