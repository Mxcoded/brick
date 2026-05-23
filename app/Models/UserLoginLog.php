<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device_type',
        'location',
        'login_type',
        'status',
        'failure_reason',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    /**
     * Get the user that owns this login log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful logins only.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed logins only.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for logins within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_in_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today's logins.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('logged_in_at', today());
    }

    /**
     * Get session duration in minutes.
     */
    public function getSessionDurationAttribute(): ?int
    {
        if (!$this->logged_out_at) {
            return null;
        }

        return $this->logged_in_at->diffInMinutes($this->logged_out_at);
    }

    /**
     * Check if session is still active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'success' && is_null($this->logged_out_at);
    }
}
