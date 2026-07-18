<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StaffSetting extends Model
{
    use HasProperty;

    protected $fillable = ['key', 'value', 'property_id'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'staff_setting_'.$key.'_'.(app(PropertyService::class)->id() ?? 'global');

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('staff_setting_'.$key.'_'.(app(PropertyService::class)->id() ?? 'global'));
    }
}
