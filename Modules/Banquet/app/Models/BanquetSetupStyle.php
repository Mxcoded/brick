<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanquetSetupStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path'
    ];
}
