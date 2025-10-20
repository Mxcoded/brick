<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'full_name',
        'nationality',
        'contact_number',
        'birthday',
        'email',
        'gender',
        'occupation',
        'company_name',
        'home_address',
        'emergency_name',
        'emergency_relationship',
        'emergency_contact',
        'last_visit_at',
        'visit_count',
        'opt_in_data_save',
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_visit_at' => 'datetime',
        'opt_in_data_save' => 'boolean',
        'visit_count' => 'integer',
    ];

    // Relationships
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(GuestPreference::class);
    }

    // Scopes
    public function scopeRecentVisitors($query, $days = 30)
    {
        return $query->where('last_visit_at', '>=', now()->subDays($days));
    }

    // Accessor for full profile
    public function getFullProfileAttribute()
    {
        return [
            'name' => $this->full_name,
            'contact' => $this->contact_number,
            'email' => $this->email,
            'address' => $this->home_address,
            'emergency' => $this->emergency_name . ' (' . $this->emergency_relationship . ')',
            'preferences' => $this->preference?->preferences ?? [],
        ];
    }
}
