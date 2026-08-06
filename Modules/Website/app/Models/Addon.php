<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An upsell / add-on that guests can attach to a booking at checkout
 * (e.g. airport pickup, extra breakfast, late checkout).
 */
class Addon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'is_per_night',
        'is_active',
        'icon',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_per_night' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: only active add-ons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: order by sort order, then name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Bookings that selected this add-on.
     */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_addon')
            ->withPivot(['name', 'price', 'is_per_night', 'quantity', 'total'])
            ->withTimestamps();
    }

    /**
     * Line-item total for this add-on on a stay.
     *
     * Per-night add-ons scale with the number of nights; one-time add-ons
     * are charged once regardless of stay length.
     */
    public function totalFor(int $nights, int $quantity = 1): float
    {
        $qty = max(1, $quantity);
        $base = (float) $this->price * $qty;

        return $this->is_per_night ? $base * max(1, $nights) : $base;
    }
}
