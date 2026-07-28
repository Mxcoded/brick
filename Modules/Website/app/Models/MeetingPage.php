<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MeetingPage extends Model implements AuditableContract
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_image',
        'stats',
        'brochure_pdf',
        'equipment_heading',
        'equipment_items',
        'catering_heading',
        'catering_description',
        'catering_image_1',
        'catering_image_2',
        'catering_image_3',
        'contact_phone',
        'contact_email',
        'seo_title',
        'seo_description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'equipment_items' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function rooms()
    {
        return $this->hasMany(MeetingRoom::class)->orderBy('sort_order');
    }

    public function gallery()
    {
        return $this->hasMany(MeetingGallery::class)->orderBy('sort_order');
    }

    use Auditable;
}
