<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Website\Database\Factories\AmenityFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Amenity extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'icon'];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'amenity_room');
    }

    // protected static function newFactory(): AmenityFactory
    // {
    //     // return AmenityFactory::new();
    // }
}
