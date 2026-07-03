<?php

namespace Modules\Staff\Exports;

use Modules\Staff\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StaffExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected ?string $branchFilter;
    protected ?string $statusFilter;

    public function __construct(?string $branchFilter = null, ?string $statusFilter = null)
    {
        $this->branchFilter = $branchFilter;
        $this->statusFilter = $statusFilter;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Employee::query();

        // Apply branch filter
        if ($this->branchFilter) {
            $query->whereRaw('LOWER(branch_name) = ?', [strtolower($this->branchFilter)]);
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Staff Code',
            'Full Name',
            'Email',
            'Phone Number',
            'Position',
            'Department',
            'Branch',
            'Gender',
            'Date of Birth',
            'Marital Status',
            'State of Origin',
            'LGA',
            'Nationality',
            'Residential Address',
            'Start Date',
            'End Date',
            'Status',
            'NIN',
            'BVN',
            'Next of Kin Name',
            'Next of Kin Phone',
            'Emergency Contact Name',
            'Emergency Contact Phone',
        ];
    }

    /**
     * @param Employee $employee
     * @return array
     */
    public function map($employee): array
    {
        return [
            $employee->staff_code ?? 'N/A',
            strtoupper($employee->name),
            $employee->email,
            $employee->phone_number,
            $employee->position,
            $employee->department ?? 'N/A',
            $employee->branch_name ?? 'Not Assigned',
            $employee->gender,
            $employee->date_of_birth ? Carbon::parse($employee->date_of_birth)->format('d/m/Y') : 'N/A',
            $employee->marital_status,
            $employee->state_of_origin,
            $employee->lga,
            $employee->nationality,
            $employee->residential_address,
            $employee->start_date ? Carbon::parse($employee->start_date)->format('d/m/Y') : 'N/A',
            $employee->end_date ? Carbon::parse($employee->end_date)->format('d/m/Y') : 'Active',
            ucfirst($employee->status),
            $employee->nin ? $this->maskSensitiveData($employee->nin) : 'N/A',
            $employee->bvn ? $this->maskSensitiveData($employee->bvn) : 'N/A',
            $employee->next_of_kin_name,
            $employee->next_of_kin_phone,
            $employee->ice_contact_name,
            $employee->ice_contact_phone,
        ];
    }

    /**
     * Mask sensitive data like NIN/BVN
     */
    private function maskSensitiveData(string $value): string
    {
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }
        return str_repeat('*', strlen($value) - 4) . substr($value, -4);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $title = 'Staff Data';
        if ($this->branchFilter) {
            $title .= ' - ' . ucfirst($this->branchFilter) . ' Branch';
        }
        return $title;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1a472a'], // Brickspoint green
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // All cells border
            "A1:{$highestColumn}{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
