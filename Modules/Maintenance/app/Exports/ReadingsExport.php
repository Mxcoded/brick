<?php

namespace Modules\Maintenance\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Maintenance\Models\MaintenanceReading;

class ReadingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $from;

    protected $to;

    protected $readingType;

    public function __construct($from = null, $to = null, $readingType = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->readingType = $readingType;
    }

    public function collection()
    {
        $query = MaintenanceReading::with('recorder');

        if ($this->from) {
            $query->where('reading_date', '>=', $this->from);
        }
        if ($this->to) {
            $query->where('reading_date', '<=', $this->to);
        }
        if ($this->readingType) {
            $query->where('reading_type', $this->readingType);
        }

        return $query->orderByDesc('reading_date')->orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Category',
            'Reading',
            'Capacity',
            'Calculated',
            'Notes',
            'Recorded By',
        ];
    }

    public function map($reading): array
    {
        $types = MaintenanceReading::TYPES;

        $readingDisplay = match ($reading->reading_type) {
            'cold_room' => number_format($reading->reading_value, 1).'°C',
            'diesel_reservoir' => number_format($reading->reading_value, 0).'L',
            default => number_format($reading->reading_value, 1).'%',
        };

        $calculatedDisplay = match ($reading->reading_type) {
            'diesel_reservoir' => '—',
            default => $reading->calculated_value
                ? number_format($reading->calculated_value, $reading->reading_type === 'generator' ? 2 : 0)
                : '—',
        };

        return [
            $reading->reading_date->format('M d, Y'),
            $types[$reading->reading_type] ?? $reading->reading_type,
            $reading->category ? ucfirst(str_replace('_', ' ', $reading->category)) : '—',
            $readingDisplay,
            $reading->capacity ? number_format($reading->capacity) : '—',
            $calculatedDisplay,
            $reading->notes ?: '—',
            $reading->recorder?->name ?: '—',
        ];
    }
}
