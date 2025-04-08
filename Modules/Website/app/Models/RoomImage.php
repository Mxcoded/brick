<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\RoomImageFactory;

class RoomImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['room_id', 'path', 'type', 'order', 'caption'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // protected static function newFactory(): RoomImageFactory
    // {
    //     // return RoomImageFactory::new();
    // }
}
