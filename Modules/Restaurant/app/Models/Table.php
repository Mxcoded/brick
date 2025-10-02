<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\TableFactory;

class Table extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['number'];
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
