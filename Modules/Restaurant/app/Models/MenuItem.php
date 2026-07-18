<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, HasProperty, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['restaurant_menu_categories_id', 'name',
        'image', 'description', 'price', 'is_available', 'property_id'];

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
