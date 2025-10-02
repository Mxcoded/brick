<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPreference extends Model
{
    use HasFactory;

    protected $table = 'guest_preferences'; // Explicit for clarity

    protected $fillable = [
        'guest_id',
        'preferences', // JSON array
    ];

    protected $casts = [
        'preferences' => 'json', // Auto-casts to array for easy access
    ];

    /**
     * Relationship to Guest.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Mutator for setting preferences (ensures JSON).
     */
    public function setPreferencesAttribute(array $value): void
    {
        $this->attributes['preferences'] = json_encode($value);
    }

    /**
     * Accessor to get preferences as array.
     */
    public function getPreferencesAttribute(): array
    {
        return $this->attributes['preferences'] ?? [];
    }

    /**
     * Helper methods for common prefs (extend as needed based on form).
     */
    public function getPreferredRoomTypeAttribute(): ?string
    {
        return $this->preferences['preferred_room_type'] ?? null;
    }

    public function setPreferredRoomTypeAttribute(?string $value): void
    {
        $this->preferences['preferred_room_type'] = $value;
        $this->save();
    }

    public function getBedBreakfastIncludedAttribute(): bool
    {
        return $this->preferences['bb_included'] ?? false;
    }

    // Scope for guests with specific prefs
    public function scopeWithPreference($query, string $key, $value = null)
    {
        return $query->whereJsonContains('preferences->' . $key, $value);
    }
}
