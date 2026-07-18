<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Restaurant\Database\Factories\TableFactory;

class Table extends Model
{
    use HasFactory, HasProperty;

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
