<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OffersPage extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'intro_heading',
        'intro_description',
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'offers_page_id')->orderBy('sort_order');
    }
}
