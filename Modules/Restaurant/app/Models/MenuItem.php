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
   

    protected $fillable = ['restaurant_categories_id', 'name', 'description', 'price'];
    protected $table = 'restaurant_menu_items';
    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'restaurant_categories_id');
    }

    // protected static function newFactory(): MenuItemFactory
    // {
    //     // return MenuItemFactory::new();
    // }
}
