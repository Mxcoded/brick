<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RestockLog extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = ['item_id', 'store_id', 'quantity', 'total_cost', 'lot_number', 'restocked_by', 'restocked_by_id'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function restockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restocked_by_id');
    }
}
