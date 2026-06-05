<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Staff\Models\Employee;
use Modules\Website\Models\GuestProfile;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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

    public function isStaff(): bool
    {
        return $this->type === 'staff';
    }

    public function isGuest(): bool
    {
        return $this->type === 'guest';
    }

    public function scopeStaff($query)
    {
        return $query->where('type', 'staff');
    }

    public function scopeGuest($query)
    {
        return $query->where('type', 'guest');
    }
}
