<?php

namespace Modules\Admin\Models;

use App\Models\Traits\HasProperty;
use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PropertySetting extends Model
{
    use HasProperty;

    protected $fillable = [
        'property_id',
        'group',
        'key',
        'value',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * Get a setting value by group and key.
     */
    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $propertyId = app(PropertyService::class)->id();

        if (! $propertyId) {
            return $default;
        }

        $cacheKey = "property_settings:{$propertyId}:{$group}:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $propertyId, $default) {
            $setting = static::where('property_id', $propertyId)
                ->where('group', $group)
                ->where('key', $key)
                ->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value by group and key.
     */
    public static function setValue(string $group, string $key, mixed $value): void
    {
        $propertyId = app(PropertyService::class)->id();

        if (! $propertyId) {
            return;
        }

        static::updateOrCreate(
            [
                'property_id' => $propertyId,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
                'type' => gettype($value),
            ]
        );

        static::clearCache($group, $key);
    }

    /**
     * Get all settings for a group.
     */
    public static function getGroup(string $group): array
    {
        $propertyId = app(PropertyService::class)->id();

        if (! $propertyId) {
            return [];
        }

        $cacheKey = "property_settings:{$propertyId}:{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $propertyId) {
            return static::where('property_id', $propertyId)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Set multiple settings for a group.
     */
    public static function setGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::setValue($group, $key, $value);
        }
    }

    /**
     * Get all settings for the current property.
     */
    public static function getAll(): array
    {
        $propertyId = app(PropertyService::class)->id();

        if (! $propertyId) {
            return [];
        }

        return static::where('property_id', $propertyId)
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Clear cache for a specific setting.
     */
    public static function clearCache(string $group, ?string $key = null): void
    {
        $propertyId = app(PropertyService::class)->id();

        if (! $propertyId) {
            return;
        }

        if ($key) {
            Cache::forget("property_settings:{$propertyId}:{$group}:{$key}");
        }

        Cache::forget("property_settings:{$propertyId}:{$group}");
    }
}
