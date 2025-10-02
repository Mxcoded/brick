<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Department
 * Represents a department or team within a store.
 */
class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'store_id'];

    /**
     * Get the store that the department belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the usage logs for this department.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }
}
