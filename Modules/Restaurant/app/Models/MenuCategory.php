<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Restaurant\Database\Factories\MenuCategoryFactory;

class MenuCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name'];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    // protected static function newFactory(): MenuCategoryFactory
    // {
    //     // return MenuCategoryFactory::new();
    // }
}
