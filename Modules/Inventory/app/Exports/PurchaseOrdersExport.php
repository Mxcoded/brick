<?php

namespace Modules\Inventory\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Inventory\Models\PurchaseOrder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?string $status;

    protected ?int $supplierId;

    public function __construct(?string $status = null, ?int $supplierId = null)
    {
        $this->status = $status;
        $this->supplierId = $supplierId;
    }

    public function collection()
    {
        $query = PurchaseOrder::with('supplier', 'store', 'createdBy', 'items');

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->supplierId) {
            $query->where('supplier_id', $this->supplierId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Supplier',
            'Store',
            'Status',
            'Items Count',
            'Notes',
            'Created By',
            'Created At',
            'Approved At',
        ];
    }

    public function map($po): array
    {
        return [
            $po->po_number,
            $po->supplier->name ?? 'N/A',
            $po->store->name ?? 'N/A',
            ucfirst($po->status),
            $po->items->sum('quantity'),
            $po->notes ?? '—',
            $po->createdBy->name ?? 'N/A',
            $po->created_at->format('d/m/Y H:i'),
            $po->approved_at ? Carbon::parse($po->approved_at)->format('d/m/Y H:i') : '—',
        ];
    }

    public function title(): string
    {
        $title = 'Purchase Orders';
        if ($this->status) {
            $title .= ' - '.ucfirst($this->status);
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
