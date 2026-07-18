<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'code',
        'address',
        'city',
        'state',
        'country',
        'contact_email',
        'contact_phone',
        'currency',
        'timezone',
        'is_active',
        'is_headquarters',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_headquarters' => 'boolean',
        'settings' => 'json',
    ];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'property_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDefault(): ?self
    {
        if (! Schema::hasTable('properties')) {
            return null;
        }

        $propertyId = session('current_property_id');

        if ($propertyId) {
            return self::find($propertyId);
        }

        $user = auth()->user();
        if ($user) {
            $defaultPivot = $user->properties()->wherePivot('is_default', true)->first();
            if ($defaultPivot) {
                session(['current_property_id' => $defaultPivot->id]);

                return $defaultPivot;
            }

            $firstProperty = $user->properties()->first();
            if ($firstProperty) {
                session(['current_property_id' => $firstProperty->id]);

                return $firstProperty;
            }
        }

        return null;
    }

    public static function current(): ?self
    {
        return self::getDefault();
    }

    public function getTaxRate(): float
    {
        return (float) ($this->settings['tax_rate'] ?? 7.5);
    }

    public function getCurrencySymbol(): string
    {
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }

    public function getWebsiteSettings(): array
    {
        return $this->settings['website'] ?? [];
    }
}
