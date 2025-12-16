<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanquetVenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'rate_per_hour',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_per_hour' => 'decimal:2',
    ];

    /**
     * The "children" venues that make up this venue.
     * Use case: If this is "Adamawa + Kano Hall", this returns ["Adamawa Hall", "Kano Hall"].
     */
    public function subVenues()
    {
        return $this->belongsToMany(
            BanquetVenue::class,
            'banquet_venue_combinations',
            'parent_venue_id',
            'child_venue_id'
        );
    }

    /**
     * The "parent" venues that this venue is a part of.
     * Use case: If this is "Adamawa Hall", this returns ["Adamawa + Kano Hall"].
     */
    public function parentVenues()
    {
        return $this->belongsToMany(
            BanquetVenue::class,
            'banquet_venue_combinations',
            'child_venue_id',
            'parent_venue_id'
        );
    }

    /**
     * Helper to get all conflicting Venue IDs (Self + Parents + Children)
     * This is used by the Controller to block all related rooms.
     */
    public function getConflictingVenueIds()
    {
        // 1. Start with self
        $ids = collect([$this->id]);

        // 2. Add children (If I am booked, my parts are also busy)
        $ids = $ids->merge($this->subVenues()->pluck('banquet_venues.id'));

        // 3. Add parents (If I am booked, the combination containing me is also busy)
        $ids = $ids->merge($this->parentVenues()->pluck('banquet_venues.id'));

        return $ids->unique()->values()->toArray();
    }
}
