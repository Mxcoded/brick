<?php

namespace Modules\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ContactMessage extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'is_archived',
        'archived_at',
        'archived_by',
        'last_reply_at',
        'assigned_to',
        'follow_up_status',
    ];

    protected $casts = [
        'status' => 'string',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all replies for this message.
     */
    public function replies()
    {
        return $this->hasMany(ContactMessageReply::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the user who archived this message.
     */
    public function archivedByUser()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Get the staff user assigned to this message.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only archived messages.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope: Only active (non-archived) messages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope: Only unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope: Awaiting reply (read but not replied).
     */
    public function scopeAwaitingReply($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope: Assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: Follow-up status filter.
     */
    public function scopeFollowUpStatus($query, $status)
    {
        return $query->where('follow_up_status', $status);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Archive this message.
     */
    public function archive(?int $userId = null): bool
    {
        return $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => $userId,
        ]);
    }

    /**
     * Restore (unarchive) this message.
     */
    public function restore(): bool
    {
        return $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
        ]);
    }

    /**
     * Mark as replied and update last_reply_at.
     */
    public function markAsReplied(): bool
    {
        return $this->update([
            'status' => 'replied',
            'last_reply_at' => now(),
        ]);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get reply count.
     */
    public function getReplyCountAttribute()
    {
        return $this->replies()->count();
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'unread' => 'primary',
            'read' => 'warning',
            'replied' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute()
    {
        return match ($this->status) {
            'unread' => 'fa-envelope',
            'read' => 'fa-envelope-open',
            'replied' => 'fa-reply',
            default => 'fa-question',
        };
    }
}
