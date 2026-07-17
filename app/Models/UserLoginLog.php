<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    use HasFactory;

    /**
     * Session timeout in minutes - sessions inactive for longer are considered expired.
     */
    public const SESSION_TIMEOUT_MINUTES = 30;

    /**
     * Maximum session age in hours - sessions older than this are auto-expired.
     */
    public const MAX_SESSION_AGE_HOURS = 24;

    protected $fillable = [
        'user_id',
        'session_id',
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
        'last_activity_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
        'last_activity_at' => 'datetime',
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
        if (! $this->logged_out_at) {
            return null;
        }

        return $this->logged_in_at->diffInMinutes($this->logged_out_at);
    }

    /**
     * Check if session is still active (considering activity timeout).
     */
    public function getIsActiveAttribute(): bool
    {
        // Must be a successful login without explicit logout
        if ($this->status !== 'success' || ! is_null($this->logged_out_at)) {
            return false;
        }

        // Check if session has exceeded max age
        if ($this->logged_in_at->diffInHours(now()) >= self::MAX_SESSION_AGE_HOURS) {
            return false;
        }

        // Check activity timeout
        $lastActivity = $this->last_activity_at ?? $this->logged_in_at;

        return $lastActivity->diffInMinutes(now()) < self::SESSION_TIMEOUT_MINUTES;
    }

    /**
     * Check if session is stale (inactive but not logged out).
     */
    public function getIsStaleAttribute(): bool
    {
        if ($this->status !== 'success' || ! is_null($this->logged_out_at)) {
            return false;
        }

        return ! $this->is_active;
    }

    /**
     * Scope for truly active sessions (within activity timeout).
     */
    public function scopeActive($query)
    {
        $timeout = now()->subMinutes(self::SESSION_TIMEOUT_MINUTES);
        $maxAge = now()->subHours(self::MAX_SESSION_AGE_HOURS);

        return $query->where('status', 'success')
            ->whereNull('logged_out_at')
            ->where('logged_in_at', '>=', $maxAge)
            ->where(function ($q) use ($timeout) {
                $q->where('last_activity_at', '>=', $timeout)
                    ->orWhere(function ($q2) use ($timeout) {
                        $q2->whereNull('last_activity_at')
                            ->where('logged_in_at', '>=', $timeout);
                    });
            });
    }

    /**
     * Scope for stale sessions (logged in but inactive beyond timeout).
     */
    public function scopeStale($query)
    {
        $timeout = now()->subMinutes(self::SESSION_TIMEOUT_MINUTES);
        $maxAge = now()->subHours(self::MAX_SESSION_AGE_HOURS);

        return $query->where('status', 'success')
            ->whereNull('logged_out_at')
            ->where(function ($q) use ($timeout, $maxAge) {
                // Either too old
                $q->where('logged_in_at', '<', $maxAge)
                  // Or inactive beyond timeout
                    ->orWhere(function ($q2) use ($timeout) {
                        $q2->where('last_activity_at', '<', $timeout);
                    })
                    ->orWhere(function ($q2) use ($timeout) {
                        $q2->whereNull('last_activity_at')
                            ->where('logged_in_at', '<', $timeout);
                    });
            });
    }
}
