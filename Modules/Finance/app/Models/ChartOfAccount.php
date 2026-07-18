<?php

namespace Modules\Finance\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasProperty, SoftDeletes;

    protected $table = 'finance_chart_of_accounts';

    protected $fillable = [
        'property_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'is_contra',
        'active',
        'description',
    ];

    protected $casts = [
        'is_contra' => 'boolean',
        'active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isDebitNormal(): bool
    {
        return $this->normal_balance === 'debit';
    }
}
