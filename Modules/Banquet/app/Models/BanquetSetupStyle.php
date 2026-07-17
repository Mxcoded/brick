<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetSetupStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
    ];
}
