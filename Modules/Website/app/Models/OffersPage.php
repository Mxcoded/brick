<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OffersPage extends Model implements AuditableContract
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

    use Auditable;
}
