<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
// use Modules\Website\Database\Factories\GuestProfileFactory;

class GuestProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array', // Cast JSON preferences to array
    ];

    /**
     * Get the user that owns the guest profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // protected static function newFactory(): GuestProfileFactory
    // {
    //     // return GuestProfileFactory::new();
    // }
}
