<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

// use Modules\Restaurant\Database\Factories\TableFactory;

class Table extends Model implements AuditableInterface
{
    use Auditable, HasFactory, HasProperty;

    protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['number', 'capacity', 'section', 'property_id'];

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
