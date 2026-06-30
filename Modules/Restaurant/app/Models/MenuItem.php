<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['restaurant_menu_categories_id', 'name',
        'image', 'description', 'price'];

    protected $table = 'restaurant_menu_items';

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'restaurant_menu_categories_id')->withTrashed();
    }
}
