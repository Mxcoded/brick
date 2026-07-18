<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// use Modules\Website\Database\Factories\SettingsFactory;

class Settings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    // protected static function newFactory(): SettingsFactory
    // {
    //     // return SettingsFactory::new();
    // }

    public static function getAllCached(): array
    {
        return Cache::remember('website_settings_all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('website_settings_all');
    }
}
