<?php

namespace Modules\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessageReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_message_id',
        'user_id',
        'message',
        'direction',
        'is_read',
        'email_message_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the parent contact message.
     */
    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class);
    }

    /**
     * Get the user (staff) who sent this reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only outgoing (staff) replies.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope: Only incoming (guest) replies.
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope: Only unread replies.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get sender name based on direction.
     */
    public function getSenderNameAttribute()
    {
        if ($this->direction === 'outgoing') {
            return $this->user->name ?? 'Staff';
        }
        return $this->contactMessage->name ?? 'Guest';
    }

    /**
     * Get sender email based on direction.
     */
    public function getSenderEmailAttribute()
    {
        if ($this->direction === 'outgoing') {
            return $this->user->email ?? config('mail.from.address');
        }
        return $this->contactMessage->email ?? '';
    }

    /**
     * Check if this is a staff reply.
     */
    public function getIsStaffReplyAttribute()
    {
        return $this->direction === 'outgoing';
    }
}
