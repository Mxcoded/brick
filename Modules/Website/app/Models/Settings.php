<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Website\Database\Factories\SettingsFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Settings extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
