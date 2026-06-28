<?php

namespace Modules\Website\Models;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
