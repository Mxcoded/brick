<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanquetOrderDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'banquet_order_id',
        'event_date',
        'event_description',
        'guest_count',
        'event_status',
        'event_type',
        'room', // Kept for backward compatibility until data migration is done
        'setup_style', // Kept for backward compatibility
        'banquet_venue_id', // New Foreign Key
        'banquet_setup_style_id', // New Foreign Key
        'start_time',
        'end_time',
        'duration_minutes'
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function banquetOrder()
    {
        return $this->belongsTo(BanquetOrder::class);
    }

    public function menuItems()
    {
        return $this->hasMany(BanquetOrderMenuItem::class);
    }

    /**
     * Get the venue associated with this event day.
     */
    public function venue()
    {
        return $this->belongsTo(BanquetVenue::class, 'banquet_venue_id');
    }

    /**
     * Get the setup style associated with this event day.
     */
    public function style()
    {
        return $this->belongsTo(BanquetSetupStyle::class, 'banquet_setup_style_id');
    }
}
