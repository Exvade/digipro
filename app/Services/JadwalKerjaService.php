<?php

namespace App\Services;

use App\Models\JadwalKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JadwalKerjaService
{
    public function getIndexData($request)
    {
        $user = Auth::user();
        if (in_array($user->role, ['operator', 'leader', 'foreman'])) {
            return ['redirect' => route('jadwal-kerja.operator-index')];
        }

        $query = JadwalKerja::with('user');

        if (in_array($user->role, ['leader', 'foreman'])) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('tanggal')) $query->byDate($request->tanggal);
            if ($request->filled('shift')) $query->byShift($request->shift);
            if ($request->filled('bagian')) {
                $query->whereHas('user', fn($q) => $q->where('bagian', $request->bagian));
            }
        }

        return [
            'view' => 'jadwal-kerja.index',
            'data' => [
                'jadwalKerjas' => $query->orderBy('tanggal', 'desc')->paginate(10),
                'users' => User::where('departemen', 'produksi')->orderBy('nama')->get()
            ]
        ];
    }

    public function getOperatorIndexData($user, $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $minggu = $request->get('minggu');
        $hari = $request->get('hari');
        $shift = $request->get('shift');

        $jadwalHariIni = JadwalKerja::with('user')
            ->where('user_id', $user->id)
            ->where('tanggal', now()->toDateString())
            ->first();

        $query = JadwalKerja::with('user')->where('user_id', $user->id);

        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        if ($hari) $query->whereRaw('DAYOFWEEK(tanggal) = ?', [$hari + 1]);
        if ($shift) $query->where('shift', $shift);
        if ($minggu) {
            $year = $tahun ?: now()->year;
            $month = $bulan ?: now()->month;

            $startOfMonth = Carbon::create($year, $month, 1);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $weekStart = $startOfMonth->copy()->addWeeks($minggu - 1)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            if ($weekEnd->gt($endOfMonth)) $weekEnd = $endOfMonth;
            if ($weekStart->lt($startOfMonth)) $weekStart = $startOfMonth;

            $query->whereBetween('tanggal', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        $filteredJadwal = $query->orderBy('tanggal')->get();

        if (!$bulan && !$tahun && !$minggu && !$hari && !$shift) {
            $jadwalMingguIni = JadwalKerja::with('user')
                ->where('user_id', $user->id)
                ->whereBetween('tanggal', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->orderBy('tanggal')
                ->get();

            $jadwalBulanIni = JadwalKerja::with('user')
                ->where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->orderBy('tanggal')
                ->get();
        } else {
            $jadwalMingguIni = $filteredJadwal;
            $jadwalBulanIni = $filteredJadwal;
        }

        $stats = [
            'total_hari_kerja' => $jadwalBulanIni->where('shift', '!=', 'off')->count(),
            'shift_pagi' => $jadwalBulanIni->where('shift', 'pagi')->count(),
            'shift_siang' => $jadwalBulanIni->where('shift', 'siang')->count(),
            'shift_malam' => $jadwalBulanIni->where('shift', 'malam')->count(),
            'hari_libur' => $jadwalBulanIni->where('shift', 'off')->count(),
            'bagian' => $user->bagian ?? 'Tidak ditentukan'
        ];

        $statusHariIni = $this->getStatusHariIni($jadwalHariIni);

        return [
            'user' => $user,
            'jadwalHariIni' => $jadwalHariIni,
            'jadwalMingguIni' => $jadwalMingguIni,
            'jadwalBulanIni' => $jadwalBulanIni,
            'stats' => $stats,
            'statusHariIni' => $statusHariIni,
        ];
    }

    private function getStatusHariIni($jadwalHariIni)
    {
        if (!$jadwalHariIni) return ['status' => 'tidak_ada_jadwal', 'message' => 'Tidak ada jadwal hari ini', 'color' => 'secondary'];
        if ($jadwalHariIni->shift === 'off') return ['status' => 'libur', 'message' => 'Hari libur', 'color' => 'info'];

        $now = now();
        $jamMasuk = $jadwalHariIni->jam_masuk ? Carbon::createFromFormat('H:i:s', $jadwalHariIni->jam_masuk) : null;
        $jamKeluar = $jadwalHariIni->jam_keluar ? Carbon::createFromFormat('H:i:s', $jadwalHariIni->jam_keluar) : null;

        if ($jamMasuk && $jamKeluar) {
            if ($now->between($jamMasuk, $jamKeluar)) return ['status' => 'sedang_kerja', 'message' => 'Sedang bekerja', 'color' => 'success'];
            if ($now->lt($jamMasuk)) return ['status' => 'akan_masuk', 'message' => "Masuk dalam {$now->diffInMinutes($jamMasuk)} menit", 'color' => 'warning'];
            return ['status' => 'selesai_kerja', 'message' => 'Selesai bekerja', 'color' => 'primary'];
        }

        return ['status' => 'jadwal_tidak_lengkap', 'message' => 'Jadwal tidak lengkap', 'color' => 'danger'];
    }

    public function getUserShiftResponse($request)
    {
        $userId = $request->user_id;
        $tanggal = $request->tanggal ?: now()->toDateString();
        $ignoreId = $request->ignore_id;

        $query = JadwalKerja::where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'aktif');

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $jadwal = $query->first();

        if (!$jadwal) {
            $user = User::with('grup')->find($userId);
            $grupShift = $user && $user->grup ? $user->grup->shift : null;

            if ($grupShift && $grupShift !== 'all') {
                $shiftMap = ['pagi' => 'shift_1', 'siang' => 'shift_2', 'malam' => 'shift_3'];
                return ['success' => true, 'shift' => $shiftMap[$grupShift] ?? null, 'shift_display' => ucfirst($grupShift), 'source' => 'grup'];
            }
            return ['success' => false, 'message' => 'Tidak ada jadwal', 'shift' => null, 'shift_display' => null];
        }

        $shiftMap = ['pagi' => 'shift_1', 'siang' => 'shift_2', 'malam' => 'shift_3', 'off' => null];
        return ['success' => true, 'shift' => $shiftMap[$jadwal->shift] ?? null, 'shift_display' => $jadwal->shift === 'off' ? 'Libur' : 'Shift ' . ucfirst($jadwal->shift), 'source' => 'jadwal_kerja'];
    }

    public function storeJadwal($validated)
    {
        // Check if schedule already exists for this user and date
        $exists = JadwalKerja::where('user_id', $validated['user_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            throw new \Exception("Karyawan tersebut sudah memiliki jadwal pada tanggal " . Carbon::parse($validated['tanggal'])->format('d/m/Y') . ". Satu karyawan hanya bisa memiliki satu jadwal per hari.");
        }

        $defaultJam = JadwalKerja::getDefaultJamKerja($validated['shift']);
        $validated['jam_masuk']  = $validated['jam_masuk'] ?? $defaultJam['jam_masuk'];
        $validated['jam_keluar'] = $validated['jam_keluar'] ?? $defaultJam['jam_keluar'];
        $validated['created_by'] = Auth::id();

        return JadwalKerja::create($validated)->load('user');
    }

    public function updateJadwal($id, $validated)
    {
        $jadwal = JadwalKerja::findOrFail($id);

        // Check if changing date/user to something that already exists elsewhere
        $exists = JadwalKerja::where('user_id', $validated['user_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new \Exception("Karyawan tersebut sudah memiliki jadwal lain pada tanggal " . Carbon::parse($validated['tanggal'])->format('d/m/Y') . ".");
        }

        $defaultJam = JadwalKerja::getDefaultJamKerja($validated['shift']);
        $validated['jam_masuk']  = $validated['jam_masuk'] ?? $defaultJam['jam_masuk'];
        $validated['jam_keluar'] = $validated['jam_keluar'] ?? $defaultJam['jam_keluar'];

        $jadwal->update($validated);
        return $jadwal->load('user');
    }

    public function deleteJadwal($id)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) throw new \Exception('Unauthorized', 403);
        $jadwal = JadwalKerja::findOrFail($id);
        $jadwal->delete();
        Log::warning("🗑️ Jadwal kerja id={$id} dihapus oleh user=" . Auth::id());
    }

    public function downloadTemplateResponse()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = ['A1' => 'NIK', 'B1' => 'Nama', 'C1' => 'Tanggal', 'D1' => 'Shift', 'E1' => 'Status', 'F1' => 'Keterangan'];
            foreach ($headers as $cell => $val) $sheet->setCellValue($cell, $val);

            $sheet->getStyle('A1:F1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(30);

            $sheet->setCellValue('A2', '1234567890');
            $sheet->setCellValue('B2', 'John Doe');
            $sheet->setCellValue('C2', date('Y-m-d'));
            $sheet->setCellValue('D2', 'pagi');
            $sheet->setCellValue('E2', 'aktif');
            $sheet->setCellValue('F2', 'Contoh keterangan');

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template_Jadwal_Kerja_' . date('Y-m-d') . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('❌ Error download template: ' . $e->getMessage());
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    public function handleImportExcel($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();
            array_shift($rows);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                try {
                    $nik = $row[0];
                    $tanggal = $row[2];
                    $shift = strtolower(trim($row[3] ?? ''));
                    $status = strtolower(trim($row[4] ?? ''));

                    if (!$nik || !$tanggal || !$shift || !$status) throw new \Exception("Data tidak lengkap");
                    $user = User::where('nik', $nik)->first();
                    if (!$user) throw new \Exception("NIK {$nik} tidak ditemukan");
                    if (!in_array($shift, ['pagi', 'siang', 'malam', 'off'])) throw new \Exception("Shift tidak valid");
                    if (!in_array($status, ['aktif', 'tidak_aktif'])) throw new \Exception("Status tidak valid");

                    $tanggalParsed = is_numeric($tanggal) ? Date::excelToDateTimeObject($tanggal)->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
                    $defaultJam = JadwalKerja::getDefaultJamKerja($shift);

                    JadwalKerja::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggalParsed,
                        'shift' => $shift,
                        'status' => $status,
                        'jam_masuk' => $defaultJam['jam_masuk'],
                        'jam_keluar' => $defaultJam['jam_keluar'],
                        'keterangan' => $row[5] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            $message = "Berhasil mengimport {$imported} jadwal. " . ($skipped > 0 ? "{$skipped} dilewati. " : "");
            if (count($errors) > 0) {
                $message .= count($errors) . " gagal.";
                Log::warning('Import errors: ' . implode(';', $errors));
            }

            return $imported > 0 ? back()->with('success', $message) : back()->with('error', 'Gagal direkam. ' . implode('; ', array_slice($errors, 0, 5)));
        } catch (\Exception $e) {
            Log::error('❌ Error import excel: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengimport: ' . $e->getMessage());
        }
    }
}
