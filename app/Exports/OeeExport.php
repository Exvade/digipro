<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OeeExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $oeeData;
    protected $filters;

    public function __construct($oeeData, array $filters = [])
    {
        $this->oeeData = $oeeData instanceof Collection ? $oeeData : collect($oeeData);
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->oeeData;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Mesin',
            'Part',
            'Output',
            'Good',
            'NG',
            'Availability %',
            'Performance %',
            'Quality %',
            'OEE %',
        ];
    }

    public function map($oee): array
    {
        // Format tanggal dengan pengecekan yang lebih aman
        $tanggal = 'N/A';
        if (isset($oee->tanggal) && $oee->tanggal) {
            if ($oee->tanggal instanceof \Carbon\Carbon) {
                $tanggal = $oee->tanggal->format('Y-m-d');
            } else {
                $tanggal = $oee->tanggal;
            }
        }
        
        // Format mesin dengan fallback
        $mesin = $oee->mesin->nama_mesin ?? ($oee->planning->mesin->nama_mesin ?? 'N/A');
        
        // Format part
        $part = $oee->planning->part->no_parts ?? 'N/A';
        
        return [
            $tanggal,
            $mesin,
            $part,
            number_format($oee->actual_output ?? 0, 0),
            number_format($oee->good_output ?? 0, 0),
            number_format($oee->ng_output ?? 0, 0),
            number_format($oee->availability_rate ?? 0, 1),
            number_format($oee->performance_rate ?? 0, 1),
            number_format($oee->quality_rate ?? 0, 1),
            number_format($oee->oee_rate ?? 0, 1),
        ];
    }

    public function title(): string
    {
        $period = $this->filters['period'] ?? 'month';
        return 'OEE ' . ucfirst($period);
    }
}


