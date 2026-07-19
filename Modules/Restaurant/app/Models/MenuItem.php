<?php

namespace Modules\Restaurant\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class MenuItem extends Model implements AuditableInterface
{
    use Auditable, HasFactory, HasProperty, SoftDeletes;

    protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];

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
