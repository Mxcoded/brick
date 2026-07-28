<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Restaurant\Database\Factories\TableFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Table extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['number', 'capacity', 'section'];

    protected $table = 'restaurant_tables';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // protected static function newFactory(): TableFactory
    // {
    //     // return TableFactory::new();
    // }
}
