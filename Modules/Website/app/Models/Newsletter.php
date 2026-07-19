<?php

namespace Modules\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Newsletter extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    // Status Constants
    const STATUS_DRAFT = 'draft';

    const STATUS_SCHEDULED = 'scheduled';

    const STATUS_SENDING = 'sending';

    const STATUS_SENT = 'sent';

    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'subject',
        'preview_text',
        'content',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'sent_count',
        'failed_count',
        'opened_count',
        'clicked_count',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipients_count' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the user who created this newsletter.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all delivery logs for this newsletter.
     */
    public function deliveryLogs()
    {
        return $this->hasMany(NewsletterDeliveryLog::class);
    }

    /**
     * Get failed delivery logs for this newsletter.
     */
    public function failedDeliveries()
    {
        return $this->hasMany(NewsletterDeliveryLog::class)->where('status', 'failed');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only draft newsletters.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope: Only scheduled newsletters.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope: Only sent newsletters.
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope: Newsletters ready to be sent (scheduled and past due).
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SCHEDULED => 'info',
            self::STATUS_SENDING => 'warning',
            self::STATUS_SENT => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'light',
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'fa-file-alt',
            self::STATUS_SCHEDULED => 'fa-clock',
            self::STATUS_SENDING => 'fa-spinner fa-spin',
            self::STATUS_SENT => 'fa-check-circle',
            self::STATUS_FAILED => 'fa-exclamation-circle',
            default => 'fa-circle',
        };
    }

    /**
     * Get delivery rate percentage.
     */
    public function getDeliveryRateAttribute()
    {
        if ($this->recipients_count === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->recipients_count) * 100, 1);
    }

    /**
     * Get open rate percentage.
     */
    public function getOpenRateAttribute()
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->opened_count / $this->sent_count) * 100, 1);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Check if newsletter can be edited.
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    /**
     * Check if newsletter can be sent.
     */
    public function canSend()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    /**
     * Mark as sending.
     */
    public function markAsSending(int $recipientsCount)
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'recipients_count' => $recipientsCount,
        ]);
    }

    /**
     * Mark as sent.
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Increment sent count.
     */
    public function incrementSentCount()
    {
        $this->increment('sent_count');
    }

    /**
     * Increment failed count.
     */
    public function incrementFailedCount()
    {
        $this->increment('failed_count');
    }

    /**
     * Duplicate this newsletter as a new draft.
     */
    public function duplicate()
    {
        return self::create([
            'subject' => $this->subject.' (Copy)',
            'preview_text' => $this->preview_text,
            'content' => $this->content,
            'status' => self::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);
    }
}