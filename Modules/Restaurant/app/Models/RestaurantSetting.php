<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RestaurantSetting extends Model
{
    use HasProperty;

    protected $table = 'restaurant_settings';

    protected $fillable = ['key', 'value', 'property_id'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $propertyId = app(PropertyService::class)->id();
        $cacheKey = "restaurant_setting_{$propertyId}_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        $propertyId = app(PropertyService::class)->id();
        Cache::forget("restaurant_setting_{$propertyId}_{$key}");
    }
}
