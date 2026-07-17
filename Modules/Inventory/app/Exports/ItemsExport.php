<?php

namespace Modules\Inventory\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Inventory\Models\Item;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?string $category;

    protected ?int $supplierId;

    public function __construct(?string $category = null, ?int $supplierId = null)
    {
        $this->category = $category;
        $this->supplierId = $supplierId;
    }

    public function collection()
    {
        $query = Item::with('supplier', 'storeItems.store');

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->supplierId) {
            $query->where('supplier_id', $this->supplierId);
        }

        return $query->orderBy('description')->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Description',
            'Category',
            'Supplier',
            'Unit',
            'Unit Value',
            'Price',
            'Min Stock',
            'Max Stock',
            'Total Quantity',
            'Total Value',
            'Stores',
        ];
    }

    public function map($item): array
    {
        $storeNames = $item->storeItems->groupBy(fn ($si) => $si->store->name ?? 'Unknown')
            ->map(fn ($items, $store) => $store.': '.$items->sum('quantity'))
            ->implode(', ');

        return [
            $item->sku ?? '—',
            $item->description,
            $item->category ?? 'N/A',
            $item->supplier->name ?? 'N/A',
            $item->unit_of_measurement ?? 'N/A',
            $item->unit_value ?? 'N/A',
            number_format($item->price ?? 0, 2),
            $item->min_stock ?? 'N/A',
            $item->max_stock ?? 'N/A',
            $item->storeItems->sum('quantity'),
            number_format($item->storeItems->sum('total_cost'), 2),
            $storeNames ?: 'N/A',
        ];
    }

    public function title(): string
    {
        $title = 'Inventory Items';
        if ($this->category) {
            $title .= ' - '.$this->category;
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
