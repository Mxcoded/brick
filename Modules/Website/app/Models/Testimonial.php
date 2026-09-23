<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Website\Database\Factories\TestimonialFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Testimonial extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    const TYPES = ['stay', 'restaurant', 'event'];

    protected $fillable = [
        'guest_name',
        'email',
        'text',
        'rating',
        'cleanliness',
        'wifi_rating',
        'staff_rating',
        'food_rating',
        'maintenance_rating',
        'guest_image',
        'stay_type',
        'approved',
        'type',
        'dining_venue',
        'event_name',
        'location',
        'ap_name',
        'ap_mac',
        'ssid',
        'client_mac',
        'client_ip',
        'portal_session',
        'wifi_meta',
    ];

    protected $casts = [
        'wifi_meta' => 'array',
    ];

    public function scopeStay($q)
    {
        $q->where('type', 'stay');
    }

    public function scopeRestaurant($q)
    {
        $q->where('type', 'restaurant');
    }

    public function scopeEvent($q)
    {
        $q->where('type', 'event');
    }

    public function scopeApproved($q)
    {
        $q->where('approved', true);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'restaurant' => 'Restaurant',
            'event' => 'Event',
            default => 'Stay',
        };
    }

    public function contextLabel(): string
    {
        return match ($this->type) {
            'restaurant' => $this->dining_venue ? "Dined at {$this->dining_venue}" : 'Dining Guest',
            'event' => $this->event_name ? "Attended {$this->event_name}" : 'Event Attendee',
            default => $this->stay_type ?? 'Verified Guest',
        };
    }

    public function categoryRatings(): array
    {
        return [
            'Room Cleanliness' => $this->cleanliness,
            'Wi-Fi Experience' => $this->wifi_rating,
            'Staff Service' => $this->staff_rating,
            'Food & Restaurant' => $this->food_rating,
            'Maintenance' => $this->maintenance_rating,
        ];
    }
}
