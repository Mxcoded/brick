<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class BanquetSetupStyle extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'image_path',
    ];
}
