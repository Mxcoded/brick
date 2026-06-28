<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NightAudit extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'audit_date',
        'status',
        'started_at',
        'completed_at',
        'started_by',
        'completed_by',
        'checked_in_count',
        'occupancy_count',
        'total_rooms',
        'occupancy_percentage',
        'room_revenue',
        'extra_revenue',
        'tax_amount',
        'total_revenue',
        'total_payments',
        'charges_posted',
        'payments_count',
        'notes',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'occupancy_percentage' => 'decimal:2',
        'room_revenue' => 'decimal:2',
        'extra_revenue' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'total_payments' => 'decimal:2',
    ];

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NightAuditLog::class);
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('audit_date', $date);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeRolledBack(Builder $query): Builder
    {
        return $query->where('status', 'rolled_back');
    }
}
