<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class NewsletterSubscriber extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'unsubscribe_token',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate unsubscribe token when creating a new subscriber
        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
        });
    }

    /**
     * Ensure subscriber has an unsubscribe token.
     * Call this before sending emails to existing subscribers.
     */
    public function ensureUnsubscribeToken(): self
    {
        if (empty($this->unsubscribe_token)) {
            $this->unsubscribe_token = Str::random(64);
            $this->save();
        }

        return $this;
    }

    /**
     * Scope for active subscribers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
