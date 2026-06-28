<?php

namespace Modules\Website\Models;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $fillable = ['room_type_id', 'image_url', 'path'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
