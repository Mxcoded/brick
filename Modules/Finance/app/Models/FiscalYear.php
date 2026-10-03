<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FiscalYear extends Model implements AuditableContract
{
    protected $table = 'finance_fiscal_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function periods(): HasMany
    {
        return $this->hasMany(FiscalPeriod::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function current(): ?self
    {
        return static::active()->orderByDesc('start_date')->first();
    }

    /**
     * The period covering the given date, if this year is configured one.
     */
    public function periodFor(Carbon|string $date): ?FiscalPeriod
    {
        $date = $date instanceof Carbon ? $date->toDateString() : $date;

        return $this->periods()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }

    /**
     * Creates the monthly periods for this year, clipped to the year bounds so a
     * year that starts or ends mid-month still gets a continuous set.
     */
    public function generateMonthlyPeriods(int $monthlyCount = 12): void
    {
        $cursor = $this->start_date->copy()->startOfMonth();
        $created = 0;

        while ($created < $monthlyCount) {
            $monthStart = $cursor->copy();
            $monthEnd = $cursor->copy()->endOfMonth();

            if ($monthEnd->lt($this->start_date)) {
                $cursor->addMonthNoOverflow();

                continue;
            }

            if ($monthStart->gt($this->end_date)) {
                break;
            }

            $start = $monthStart->lt($this->start_date) ? $this->start_date->copy() : $monthStart;
            $end = $monthEnd->gt($this->end_date) ? $this->end_date->copy() : $monthEnd;

            $this->periods()->firstOrCreate(
                ['start_date' => $start->toDateString()],
                [
                    'name' => $start->format('M Y'),
                    'end_date' => $end->toDateString(),
                ],
            );

            $cursor->addMonthNoOverflow();
            $created++;
        }
    }

    public function close(): void
    {
        $this->periods()->where('status', 'open')->update([
            'status' => 'locked',
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        $this->forceFill([
            'is_closed' => true,
            'is_active' => false,
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ])->save();
    }

    use Auditable;
}
