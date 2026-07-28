<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RoomTypeImage extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = ['room_type_id', 'image_url', 'path'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
