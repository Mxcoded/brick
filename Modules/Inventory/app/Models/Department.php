<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = ['name', 'store_id', 'property_id'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ItemReturn::class);
    }
}
