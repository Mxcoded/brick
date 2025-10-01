<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Staff\Models\Employee;
use Modules\Website\Models\GuestProfile;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
    public function hasRole($roles)
    {
        Log::info('hasRole called with:', ['roles' => $roles]);
        // Handle string input (role name)
        if (is_string($roles)) {
            return $this->roles()->where('name', $roles)->exists();
        }

        // Handle single Role object
        if ($roles instanceof \Spatie\Permission\Models\Role) {
            return $this->roles()->where('id', $roles->id)->exists();
        }

        // Handle array of roles (recursively check each)
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        // Handle collection of roles (e.g., from $user->roles)
        if ($roles instanceof \Illuminate\Support\Collection) {
            return $this->hasRole($roles->all());
        }

        // Default case: return false for unrecognized input
        return false;
    }
}
