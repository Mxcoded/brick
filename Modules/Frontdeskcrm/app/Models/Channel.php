<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Channel extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'api_key',
        'api_endpoint',
        'webhook_url',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    const PROVIDERS = [
        'direct' => 'Direct / Website',
        'booking.com' => 'Booking.com',
        'expedia' => 'Expedia',
        'agoda' => 'Agoda',
        'airbnb' => 'Airbnb',
        'hotels.com' => 'Hotels.com',
        'other' => 'Other OTA',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roomMappings(): HasMany
    {
        return $this->hasMany(ChannelRoomMapping::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }
}
