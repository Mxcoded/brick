<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * A physical terminal money is taken on: a Monipoint at the front desk, a POS
 * box on the cafe counter, a separate till in the grill section.
 *
 * Not a sales point. A sales point names the counter and credits an income
 * account; this names the hardware and is debited an asset account. The two are
 * independent on purpose — one counter can take card through two different
 * banks, and one terminal can serve a cafe and a restaurant.
 */
class CollectionPoint extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'asset_account_code',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $table = 'restaurant_collection_points';

    public function orders()
    {
        return $this->hasMany(Order::class, 'collection_point_id');
    }

    public function shifts()
    {
        return $this->hasMany(WaiterShift::class, 'collection_point_id');
    }

    /**
     * @param  Builder<CollectionPoint>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The terminal to preselect on the clock-in card.
     *
     * Null when none is marked, so the waiter picks rather than the property
     * silently banking everything to whichever terminal was added first.
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
     * The asset account this terminal banks into, or null to leave the choice to
     * Finance's payment-method mapping.
     */
    public function assetCode(): ?string
    {
        return filled($this->asset_account_code) ? $this->asset_account_code : null;
    }

    public function hasOwnAccount(): bool
    {
        return $this->assetCode() !== null;
    }
}
