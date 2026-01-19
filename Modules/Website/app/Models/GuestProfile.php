<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class GuestProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'identification_type',
        'identification_number',
        'title',       // Added for CRM alignment
        'gender',      // Added for CRM alignment
        'birthday',    // Added for CRM alignment
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
        'birthday' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
