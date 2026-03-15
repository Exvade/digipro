<?php

namespace App\Http\Controllers;

use App\Models\PlanningProduksi;
use App\Models\Part;
use App\Models\Mesin;
use App\Models\Moldshop;
use App\Models\Meja;
use App\Services\PlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class PlanningExcelController extends Controller
{
    protected $planningService;

    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'A1' => 'Bagian (inject/assy)',
                'B1' => 'Tanggal (YYYY-MM-DD)',
                'C1' => 'Nama Mesin / Meja',
                'D1' => 'No Mold (Inject Only)',
                'E1' => 'No Part',
                'F1' => 'No Barang (Referensi)',
                'G1' => 'Nama Part (Referensi)',
                'H1' => 'Type (Referensi)',
                'I1' => 'Lot Produksi',
                'J1' => 'Qty Planning Total',
                'K1' => 'Target Shift Pagi',
                'L1' => 'Target Shift Siang',
                'M1' => 'Target Shift Malam',
                'N1' => 'Catatan',
                'O1' => 'Pagi 07-08',
                'P1' => 'Pagi 08-09',
                'Q1' => 'Pagi 09-10',
                'R1' => 'Pagi 10-11',
                'S1' => 'Pagi 11-12',
                'T1' => 'Pagi 12-13',
                'U1' => 'Pagi 13-14',
                'V1' => 'Pagi 14-15',
                'W1' => 'Siang 15-16',
                'X1' => 'Siang 16-17',
                'Y1' => 'Siang 17-18',
                'Z1' => 'Siang 18-19',
                'AA1' => 'Siang 19-20',
                'AB1' => 'Siang 20-21',
                'AC1' => 'Siang 21-22',
                'AD1' => 'Siang 22-23',
                'AE1' => 'Malam 23-00',
                'AF1' => 'Malam 00-01',
                'AG1' => 'Malam 01-02',
                'AH1' => 'Malam 02-03',
                'AI1' => 'Malam 03-04',
                'AJ1' => 'Malam 04-05',
                'AK1' => 'Malam 05-06',
                'AL1' => 'Malam 06-07'
            ];

            foreach ($headers as $cell => $value) $sheet->setCellValue($cell, $value);

            $sheet->getStyle('A1:AL1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ]);

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template_Planning_Produksi.xlsx"');
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate template.');
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls']);

        try {
            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();
            array_shift($rows);

            $successCount = 0;
            $errorRows = [];

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;
                $rowNum = $index + 2;

                $bagian = strtolower(trim($row[0] ?? ''));
                $tanggal = trim($row[1] ?? '');
                $namaMesinMeja = trim($row[2] ?? '');
                $noMold = trim($row[3] ?? '');
                $noPart = trim($row[4] ?? '');
                $lot = trim($row[8] ?? '');
                $qtyTotal = (int)($row[9] ?? 0);
                $targetPagi = (int)($row[10] ?? 0);
                $targetSiang = (int)($row[11] ?? 0);
                $targetMalam = (int)($row[12] ?? 0);

                if (!in_array($bagian, ['inject', 'assy'])) {
                    $errorRows[] = "Baris $rowNum: Bagian tidak valid.";
                    continue;
                }

                try {
                    $tanggalParsed = is_numeric($tanggal) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
                } catch (\Exception $e) {
                    $errorRows[] = "Baris $rowNum: Format tanggal salah.";
                    continue;
                }

                $part = Part::where('no_parts', $noPart)->first();
                if (!$part) {
                    $errorRows[] = "Baris $rowNum: Part tidak ditemukan.";
                    continue;
                }

                $mesinId = null;
                $mejaId = null;
                $moldshopId = null;
                if ($bagian === 'inject') {
                    $mesin = Mesin::where('nama_mesin', $namaMesinMeja)->first();
                    if (!$mesin) {
                        $errorRows[] = "Baris $rowNum: Mesin tidak ditemukan.";
                        continue;
                    }
                    $mesinId = $mesin->id;
                    if ($noMold) {
                        $mold = Moldshop::where('no_mold', $noMold)->first();
                        if ($mold) $moldshopId = $mold->id;
                    }
                } else {
                    $meja = Meja::where('nama_meja', $namaMesinMeja)->first();
                    if (!$meja) {
                        $errorRows[] = "Baris $rowNum: Meja tidak ditemukan.";
                        continue;
                    }
                    $mejaId = $meja->id_meja;
                }

                $planning = PlanningProduksi::create([
                    'bagian' => $bagian,
                    'tanggal' => $tanggalParsed,
                    'part_id' => $part->id,
                    'mesin_id' => $mesinId,
                    'moldshop_id' => $moldshopId,
                    'meja_id' => $mejaId,
                    'lot_produksi' => $lot,
                    'qty_planning_produksi' => $qtyTotal,
                    'target_shift_pagi' => $targetPagi,
                    'target_shift_siang' => $targetSiang,
                    'target_shift_malam' => $targetMalam,
                    'total_shift' => $targetPagi + $targetSiang + $targetMalam,
                    'status_approval' => 'pending'
                ]);

                $hourlyData = [];
                for ($i = 0; $i < 8; $i++) $hourlyData['pagi_jam_' . sprintf('%02d', 7 + $i)] = (int)($row[14 + $i] ?? 0);
                for ($i = 0; $i < 8; $i++) $hourlyData['siang_jam_' . sprintf('%02d', 15 + $i)] = (int)($row[22 + $i] ?? 0);
                $malamHours = [23, 0, 1, 2, 3, 4, 5, 6];
                foreach ($malamHours as $idx => $h) $hourlyData['malam_jam_' . sprintf('%02d', $h)] = (int)($row[30 + $idx] ?? 0);

                $this->planningService->saveHourData($planning->id, $hourlyData);
                $this->planningService->generateDigitalSignature($planning);
                $successCount++;
            }

            if ($successCount > 0) {
                return back()->with('success', "Berhasil import $successCount planning.")->with('import_errors', $errorRows);
            }
            return back()->with('error', 'Gagal import.');
        } catch (\Exception $e) {
            Log::error('Import Failure: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat import.');
        }
    }
}
