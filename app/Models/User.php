<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Staff\Models\Employee;
use Modules\Website\Models\GuestProfile;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    const STATUS_ACTIVE = 'active';

    const STATUS_SUSPENDED = 'suspended';

    const STATUS_DEACTIVATED = 'deactivated';

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'status',
        'suspended_at',
        'suspension_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function guestProfile()
    {
        return $this->hasOne(GuestProfile::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_user')->withPivot('is_default')->withTimestamps();
    }

    public function isStaff(): bool
    {
        return $this->type === 'staff';
    }

    public function isGuest(): bool
    {
        return $this->type === 'guest';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isDeactivated(): bool
    {
        return $this->status === self::STATUS_DEACTIVATED;
    }

    public function scopeStaff($query)
    {
        return $query->where('type', 'staff');
    }

    public function scopeGuest($query)
    {
        return $query->where('type', 'guest');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeDeactivated($query)
    {
        return $query->where('status', self::STATUS_DEACTIVATED);
    }
}
