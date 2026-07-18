<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;

class StaffSetting extends Model
{
    use HasProperty;

    protected $fillable = ['key', 'value', 'property_id'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
