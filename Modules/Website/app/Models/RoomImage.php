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
    protected $fillable = ['room_id', 'image_url', 'path'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

}
