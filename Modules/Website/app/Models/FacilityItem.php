<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FacilityItem extends Model implements AuditableContract
{
    protected $fillable = [
        'facilities_page_id',
        'title',
        'slug',
        'description',
        'content',
        'features',
        'image',
        'icon',
        'link_text',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(FacilitiesPage::class, 'facilities_page_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (! $item->slug) {
                $item->slug = static::generateUniqueSlug($item->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    use Auditable;
}
