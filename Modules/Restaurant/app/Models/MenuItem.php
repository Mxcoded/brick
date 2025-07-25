<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\MenuItemFactory;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['restaurant_category_id', 'name', 'description', 'price'];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'restaurant_category_id');
    }

    // protected static function newFactory(): MenuItemFactory
    // {
    //     // return MenuItemFactory::new();
    // }
}
