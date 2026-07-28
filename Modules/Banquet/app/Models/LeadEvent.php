<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LeadEvent extends Model implements AuditableContract
{
    protected $fillable = [
        'title',
        'slug',
        'code',
        'description',
        'event_date',
        'location',
        'organizer',
        'is_active',
        'hero_image',
        'hero_subtitle',
        'form_heading',
        'form_subtext',
        'thank_you_message',
        'confirmation_email_body',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function leads()
    {
        return $this->hasMany(EventLead::class, 'event_id');
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        if (! $this->hero_image) {
            return null;
        }

        return str_starts_with($this->hero_image, 'http')
            ? $this->hero_image
            : Storage::url($this->hero_image);
    }

    public function getFormHeadingOrDefault(): string
    {
        return $this->form_heading ?: 'Register Now';
    }

    public function getThankYouMessageOrDefault(): string
    {
        return $this->thank_you_message ?: 'Thank you for your interest! Our team will reach out to you shortly.';
    }

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    use Auditable;
}
