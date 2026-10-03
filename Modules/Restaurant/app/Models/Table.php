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

    /**
     * Orders taken at this table.
     *
     * The foreign key is `source_id`, not `table_id`: an order points at either a
     * restaurant table or a Website room through the same column, which is what
     * lets one `type` switch decide what `source_id` means. Left to the default,
     * this relation generated `where restaurant_orders.table_id = ?` against a
     * column that does not exist.
     *
     * Scoped to `type = 'table'` because a table id and a room id live in the
     * same column, so an unscoped match would also pull in room orders whose
     * source_id happened to equal this table's id.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'source_id')
            ->where('type', 'table');
    }

    // protected static function newFactory(): TableFactory
    // {
    //     // return TableFactory::new();
    // }
}
