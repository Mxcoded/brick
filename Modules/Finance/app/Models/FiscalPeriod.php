<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FiscalPeriod extends Model implements AuditableContract
{
    protected $table = 'finance_fiscal_periods';

    protected $fillable = [
        'fiscal_year_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('status', 'locked');
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        $date = $date instanceof Carbon ? $date->toDateString() : $date;

        return $query
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function contains(Carbon $date): bool
    {
        return $date->betweenIncluded(
            $this->start_date->startOfDay(),
            $this->end_date->endOfDay(),
        );
    }

    public function lock(): void
    {
        $this->forceFill([
            'status' => 'locked',
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ])->save();
    }

    public function unlock(): void
    {
        $this->forceFill([
            'status' => 'open',
            'locked_at' => null,
            'locked_by' => null,
        ])->save();
    }

    use Auditable;
}
