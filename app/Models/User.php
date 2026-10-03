<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Staff\Models\Employee;
use Modules\Website\Models\GuestProfile;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements AuditableContract, FilamentUser
{
    use Auditable, HasApiTokens, HasFactory, HasRoles, Notifiable;

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

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole(RoleEnum::ADMIN) || $this->can('access_admin_dashboard')) {
            return true;
        }

        // Module staff reach the shared panel through the permissions that gate
        // their own resources, without picking up admin-only pages.
        foreach ($panel->getResources() as $resource) {
            if ($resource::canAccess()) {
                return true;
            }
        }

        return false;
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function guestProfile()
    {
        return $this->hasOne(GuestProfile::class);
    }

    public function isStaff(): bool
    {
        return $this->type === 'staff';
    }

    public function isProcurementRequester(): bool
    {
        return $this->isStaff() || $this->hasRole(RoleEnum::LINE_MANAGER->value);
    }

    public function isProcurementApprover(): bool
    {
        return $this->hasAnyRole(RoleEnum::procurementApproverRoles());
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
