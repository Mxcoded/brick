<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Offer extends Model implements AuditableContract
{
    protected $fillable = [
        'offers_page_id',
        'title',
        'slug',
        'short_description',
        'content',
        'features',
        'image',
        'icon',
        'valid_from',
        'valid_to',
        'terms_conditions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(OffersPage::class, 'offers_page_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $offer) {
            if (! $offer->slug) {
                $offer->slug = static::generateUniqueSlug($offer->title);
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