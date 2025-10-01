<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\AmenityFactory;

class Amenity extends Model
{
    use HasFactory;

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
