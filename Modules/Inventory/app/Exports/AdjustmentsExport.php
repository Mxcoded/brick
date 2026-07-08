<?php

namespace Modules\Inventory\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Inventory\Models\InventoryAdjustment;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdjustmentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?string $type;

    protected ?int $storeId;

    public function __construct(?string $type = null, ?int $storeId = null)
    {
        $this->type = $type;
        $this->storeId = $storeId;
    }

    public function collection()
    {
        $query = InventoryAdjustment::with('item', 'store', 'adjustedBy');

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Item',
            'SKU',
            'Store',
            'Type',
            'Quantity Change',
            'Reason',
            'Adjusted By',
        ];
    }

    public function map($adj): array
    {
        return [
            $adj->created_at->format('d/m/Y H:i'),
            $adj->item->description ?? 'N/A',
            $adj->item->sku ?? '—',
            $adj->store->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $adj->type)),
            $adj->quantity_change,
            $adj->reason,
            $adj->adjustedBy->name ?? 'System',
        ];
    }

    public function title(): string
    {
        $title = 'Inventory Adjustments';
        if ($this->type) {
            $title .= ' - '.ucfirst(str_replace('_', ' ', $this->type));
        }

        return $title;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a472a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            "A1:{$highestColumn}{$highestRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }
}
