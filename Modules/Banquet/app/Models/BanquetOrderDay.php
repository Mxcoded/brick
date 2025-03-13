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
        'room',
        'setup_style',
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
}
