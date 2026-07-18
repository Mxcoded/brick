<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    use HasProperty;

    protected $table = 'restaurant_settings';

    protected $fillable = ['key', 'value', 'property_id'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
