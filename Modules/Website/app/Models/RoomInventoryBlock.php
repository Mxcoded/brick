<?php

namespace Modules\Website\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomInventoryBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_type_id',
        'start_date',
        'end_date',
        'blocked_count',
        'block_type',
        'min_stay',
        'max_stay',
        'stop_sell',
        'closed_to_arrival',
        'closed_to_departure',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'blocked_count' => 'integer',
        'min_stay' => 'integer',
        'max_stay' => 'integer',
        'stop_sell' => 'boolean',
        'closed_to_arrival' => 'boolean',
        'closed_to_departure' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the room type this block belongs to.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the user who created this block.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Blocks that overlap with a date range.
     */
    public function scopeOverlapping($query, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
                ->where('end_date', '>=', $start);
        });
    }

    /**
     * Scope: Blocks for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        $date = Carbon::parse($date);

        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    /**
     * Scope: Blocks for a specific room type.
     */
    public function scopeForRoomType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Scope: Only stop sell blocks.
     */
    public function scopeStopSell($query)
    {
        return $query->where('stop_sell', true);
    }

    /**
     * Scope: Only active blocks (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where('end_date', '>=', now()->format('Y-m-d'));
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this block covers a specific date.
     */
    public function coversDate($date): bool
    {
        $date = Carbon::parse($date);

        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get the number of days this block spans.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get block type label.
     */
    public function getBlockTypeLabelAttribute(): string
    {
        return match ($this->block_type) {
            'maintenance' => 'Maintenance',
            'stop_sell' => 'Stop Sell',
            'manual' => 'Manual Block',
            'overbooking_protection' => 'Overbooking Protection',
            default => ucfirst($this->block_type),
        };
    }

    /**
     * Get block type color.
     */
    public function getBlockTypeColorAttribute(): string
    {
        return match ($this->block_type) {
            'maintenance' => '#FF00FF', // Magenta
            'stop_sell' => '#dc3545', // Red
            'manual' => '#6c757d', // Gray
            'overbooking_protection' => '#fd7e14', // Orange
            default => '#6c757d',
        };
    }
}
