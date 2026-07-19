<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class Guest extends Model implements AuditableInterface
{
    use Auditable, HasFactory, SoftDeletes;

    protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'user_id',
        'title',
        'full_name',
        'nationality',
        'zip_code',
        'identification_type',
        'identification_number',
        'contact_number',
        'birthday',
        'email',
        'gender',
        'occupation',
        'company_name',
        'home_address',
        'city',
        'state',
        'emergency_name',
        'emergency_relationship',
        'emergency_contact',
        'last_visit_at',
        'visit_count',
        'opt_in_data_save',
        'loyalty_tier_id',
        'total_points',
        'lifetime_points',
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_visit_at' => 'datetime',
        'opt_in_data_save' => 'boolean',
        'visit_count' => 'integer',
        'total_points' => 'integer',
        'lifetime_points' => 'integer',
    ];

    // Relationships
    // ✅ Link to Website User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GuestDocument::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GuestMessage::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(GuestPreference::class);
    }

    public function loyaltyTier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'loyalty_tier_id');
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function recalculateTier(): void
    {
        $tier = LoyaltyTier::where('is_active', true)
            ->where('min_points', '<=', $this->lifetime_points)
            ->orderByDesc('min_points')
            ->first();

        if ($tier && $tier->id !== $this->loyalty_tier_id) {
            $this->update(['loyalty_tier_id' => $tier->id]);
        }
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
            'emergency' => $this->emergency_name.' ('.$this->emergency_relationship.')',
            'preferences' => $this->preference?->preferences ?? [],
        ];
    }
}
