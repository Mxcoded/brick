<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'event', 'name', 'sms_body', 'whatsapp_body',
        'email_subject', 'email_body', 'placeholders', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'json',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
