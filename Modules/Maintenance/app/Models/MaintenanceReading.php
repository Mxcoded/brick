<?php

namespace Modules\Maintenance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MaintenanceReading extends Model
{
    protected $fillable = [
        'reading_date',
        'reading_type',
        'category',
        'reading_value',
        'capacity',
        'calculated_value',
        'notes',
        'recorded_by',
    ];

    protected $attributes = [
        'category' => '',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_value' => 'decimal:2',
        'capacity' => 'decimal:2',
        'calculated_value' => 'decimal:2',
    ];

    public const TYPES = [
        'generator' => 'Generator Reading',
        'diesel_reservoir' => 'Diesel Reservoir',
        'water_tank' => 'Water Tank',
        'cold_room' => 'Cold Room',
    ];

    public const GENERATORS = [
        'big_gen' => ['label' => 'Big Generator', 'capacity' => 705],
        'small_gen' => ['label' => 'Small Generator', 'capacity' => 482],
    ];

    public const COLD_ROOM_TYPES = [
        'freezer' => 'Freezer',
        'fridge' => 'Fridge',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function defaultCapacity(string $category): ?float
    {
        return static::GENERATORS[$category]['capacity'] ?? null;
    }

    public function scopeOnDate($query, $date)
    {
        return $query->where('reading_date', $date);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('reading_type', $type);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('reading_date', [$from, $to]);
    }
}
