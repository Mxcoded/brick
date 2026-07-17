<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilitiesPage extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'intro_heading',
        'intro_description',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FacilityItem::class, 'facilities_page_id')->orderBy('sort_order');
    }
}
