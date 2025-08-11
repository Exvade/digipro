<?php

namespace App\Exports;

use App\Models\Checksheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ChecksheetExport implements FromArray, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $checksheets;
    protected $filters;
    protected $reportType;
    protected $summary;

    public function __construct($checksheets, $filters = [], $reportType = 'general', $summary = [])
    {
        $this->checksheets = $checksheets;
        $this->filters = $filters;
        $this->reportType = $reportType;
        $this->summary = $summary;
    }

    public function array(): array
    {
        $data = [];
        
        // Add header information
        $data[] = ['LAPORAN CHECKSHEET ' . strtoupper($this->reportType)];
        $data[] = ['DIGIPRO - Digital Production System'];
        $data[] = ['Periode: ' . Carbon::parse($this->filters['start_date'] ?? now())->format('d/m/Y') . ' - ' . Carbon::parse($this->filters['end_date'] ?? now())->format('d/m/Y')];
        $data[] = ['Dibuat pada: ' . now()->format('d/m/Y H:i') . ' oleh ' . (auth()->user()->nama ?? 'System')];
        $data[] = []; // Empty row
        
        // Add filter information
        $data[] = ['Informasi Filter'];
        $data[] = ['Jenis Laporan', ucfirst($this->reportType)];
        
        if ($this->reportType === 'inject' || $this->reportType === 'general') {
            if (!empty($this->filters['mesin_id'])) {
                $mesinName = $this->checksheets->first()->mesin->nama_mesin ?? 'Semua Mesin';
                $data[] = ['Mesin', $mesinName];
            }
        }
        
        if ($this->reportType === 'assy' || $this->reportType === 'general') {
            if (!empty($this->filters['meja_id'])) {
                $mejaName = $this->checksheets->first()->meja->nama_meja ?? 'Semua Meja';
                $data[] = ['Meja', $mejaName];
            }
        }
        
        if (!empty($this->filters['part_id'])) {
            $partName = $this->checksheets->first()->part->nama_part ?? 'Semua Part';
            $data[] = ['Part', $partName];
        }
        
        if (!empty($this->filters['user_id'])) {
            $userName = $this->checksheets->first()->user->nama ?? 'Semua User';
            $data[] = ['User', $userName];
        }
        
        if (!empty($this->filters['grup_id'])) {
            $groupName = $this->checksheets->first()->user->grup->nama_grup ?? 'Semua Grup';
            $data[] = ['Grup', $groupName];
        }
        
        if (!empty($this->filters['shift'])) {
            $data[] = ['Shift', ucfirst($this->filters['shift'])];
        }
        
        if (!empty($this->filters['approval_status'])) {
            $data[] = ['Status Approval', ucfirst($this->filters['approval_status'])];
        }
        
        $data[] = []; // Empty row
        
        // Add summary section
        $data[] = ['Ringkasan Data'];
        $data[] = ['Total Data', $this->summary['total'] ?? 0, 'Pending', $this->summary['by_status']['pending'] ?? 0, 'Approved', $this->summary['by_status']['approved'] ?? 0, 'Rejected', $this->summary['by_status']['rejected'] ?? 0];
        $data[] = []; // Empty row
        
        // Add data headers
        $headers = ['No', 'Tanggal'];
        
        if ($this->reportType === 'inject' || $this->reportType === 'general') {
            $headers[] = 'Mesin';
        }
        
        if ($this->reportType === 'assy' || $this->reportType === 'general') {
            $headers[] = 'Meja';
        }
        
        $headers = array_merge($headers, ['Part', 'User', 'Grup', 'Shift', 'Status', 'Approver', 'Keterangan']);
        $data[] = $headers;
        
        // Add data rows
        foreach ($this->checksheets as $index => $checksheet) {
            $row = [$index + 1, $checksheet->created_at ? $checksheet->created_at->format('d/m/Y H:i') : '-'];
            
            if ($this->reportType === 'inject' || $this->reportType === 'general') {
                $row[] = $checksheet->mesin ? $checksheet->mesin->nama_mesin : '-';
            }
            
            if ($this->reportType === 'assy' || $this->reportType === 'general') {
                $row[] = $checksheet->meja ? $checksheet->meja->nama_meja : '-';
            }
            
            $row = array_merge($row, [
                $checksheet->part ? $checksheet->part->nama_part : '-',
                $checksheet->user ? $checksheet->user->nama : '-',
                $checksheet->user ? ($checksheet->user->grup ? $checksheet->user->grup->nama_grup : '-') : '-',
                ucfirst($checksheet->shift ?? '-'),
                ucfirst($checksheet->approval_status ?? '-'),
                $checksheet->approver ? $checksheet->approver->nama : '-',
                $checksheet->keterangan ?? '-'
            ]);
            
            $data[] = $row;
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        
        // Header styles (rows 1-4)
        $sheet->getStyle('A1:A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Filter section styles
        $sheet->getStyle('A6:A6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        
        // Calculate summary row position
        $filterCount = 1; // Start with "Jenis Laporan"
        if ($this->reportType === 'inject' || $this->reportType === 'general') {
            if (!empty($this->filters['mesin_id'])) $filterCount++;
        }
        if ($this->reportType === 'assy' || $this->reportType === 'general') {
            if (!empty($this->filters['meja_id'])) $filterCount++;
        }
        if (!empty($this->filters['part_id'])) $filterCount++;
        if (!empty($this->filters['user_id'])) $filterCount++;
        if (!empty($this->filters['grup_id'])) $filterCount++;
        if (!empty($this->filters['shift'])) $filterCount++;
        if (!empty($this->filters['approval_status'])) $filterCount++;
        
        $summaryRow = 6 + $filterCount + 2; // 6 (header) + filterCount + 2 (empty rows)
        
        // Summary section styles
        $sheet->getStyle('A' . $summaryRow . ':A' . $summaryRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        
        // Summary data row
        $summaryDataRow = $summaryRow + 1;
        $sheet->getStyle('A' . $summaryDataRow . ':' . $lastColumn . $summaryDataRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F9F9F9'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        
        // Data headers style
        $dataHeaderRow = $summaryDataRow + 2;
        $sheet->getStyle('A' . $dataHeaderRow . ':' . $lastColumn . $dataHeaderRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Data rows style
        if ($dataHeaderRow + 1 <= $lastRow) {
            $sheet->getStyle('A' . ($dataHeaderRow + 1) . ':' . $lastColumn . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            
            // Alternate row colors
            for ($row = $dataHeaderRow + 1; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9F9F9'],
                        ],
                    ]);
                }
            }
        }
        
        // Auto-size columns
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        return $sheet;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // No
            'B' => 20,  // Tanggal
        ];
        
        $currentColumn = 'C';
        
        if ($this->reportType === 'inject' || $this->reportType === 'general') {
            $widths[$currentColumn] = 25; // Mesin
            $currentColumn = chr(ord($currentColumn) + 1);
        }
        
        if ($this->reportType === 'assy' || $this->reportType === 'general') {
            $widths[$currentColumn] = 15; // Meja
            $currentColumn = chr(ord($currentColumn) + 1);
        }
        
        $widths[$currentColumn] = 25;     // Part
        $currentColumn = chr(ord($currentColumn) + 1);
        $widths[$currentColumn] = 20;     // User
        $currentColumn = chr(ord($currentColumn) + 1);
        $widths[$currentColumn] = 10;     // Shift
        $currentColumn = chr(ord($currentColumn) + 1);
        $widths[$currentColumn] = 15;     // Status
        $currentColumn = chr(ord($currentColumn) + 1);
        $widths[$currentColumn] = 20;     // Approver
        $currentColumn = chr(ord($currentColumn) + 1);
        $widths[$currentColumn] = 30;     // Keterangan
        
        return $widths;
    }

    public function title(): string
    {
        return 'Laporan Checksheet ' . ucfirst($this->reportType);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1')->getFont()->setSize(16);
                $event->sheet->getDelegate()->getStyle('A2')->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle('A3')->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle('A4')->getFont()->setSize(12);
            },
        ];
    }
} 