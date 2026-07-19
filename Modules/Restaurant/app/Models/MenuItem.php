<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MenuItem extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['restaurant_menu_categories_id', 'name',
        'image', 'description', 'price', 'is_available'];

    protected $table = 'restaurant_menu_items';

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'restaurant_menu_categories_id')->withTrashed();
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class, 'restaurant_menu_item_id');
    }
}