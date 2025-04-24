<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
