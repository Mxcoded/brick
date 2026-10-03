<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * A selling outlet inside the venue.
 *
 * Two are enough to cover most properties (a cafe and a restaurant sharing one
 * kitchen), but nothing here assumes a number: the point is that a sale is
 * attributable to somewhere, and that somewhere can be a distinct GL revenue
 * account.
 */
class SalesPoint extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'revenue_account_code',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $table = 'restaurant_sales_points';

    public function orders()
    {
        return $this->hasMany(Order::class, 'sales_point_id');
    }

    public function shifts()
    {
        return $this->hasMany(WaiterShift::class, 'sales_point_id');
    }

    /**
     * @param  Builder<SalesPoint>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The outlet a guest order belongs to when no waiter shift applies.
     *
     * Returns null rather than the lowest id when nobody has been marked
     * default: an unattributed sale that credits the module revenue account is
     * honest, whereas quietly booking every online order to whichever outlet
     * happened to be created first is not.
     */
    public static function default(): ?self
    {
        return static::query()
            ->active()
            ->where('is_default', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * The GL revenue code this outlet posts to, or null to leave the sale on the
     * module's own revenue account.
     */
    public function revenueCode(): ?string
    {
        return filled($this->revenue_account_code) ? $this->revenue_account_code : null;
    }

    public function hasOwnRevenueAccount(): bool
    {
        return $this->revenueCode() !== null;
    }
}
