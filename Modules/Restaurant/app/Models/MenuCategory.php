<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'parent_id'];

    protected $table = 'restaurant_menu_categories';

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'restaurant_menu_categories_id');
    }

    public function parent()
    {
        return $this->belongsTo(MenuCategory::class, 'parent_id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(MenuCategory::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('menuItems');
    }
}
