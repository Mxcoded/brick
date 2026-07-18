<?php

namespace Modules\Banquet\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetEnquiry extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'property_id',
        'name',
        'email',
        'phone',
        'company',
        'event_type',
        'event_date',
        'guest_count',
        'start_time',
        'end_time',
        'setup_style',
        'catering_option',
        'accommodation_required',
        'rooms_required',
        'arrival_date',
        'departure_date',
        'parking_required',
        'site_inspection_required',
        'hear_about_us',
        'special_requirements',
        'venue_interest',
        'status',
        'admin_notes',
        'converted_to_order_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'accommodation_required' => 'boolean',
        'parking_required' => 'boolean',
        'site_inspection_required' => 'boolean',
        'arrival_date' => 'date',
        'departure_date' => 'date',
    ];

    public function convertedOrder()
    {
        return $this->belongsTo(BanquetOrder::class, 'converted_to_order_id');
    }
}
