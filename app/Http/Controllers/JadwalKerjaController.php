<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JadwalKerjaController extends Controller
{
    /** 🔍 Daftar jadwal kerja */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Redirect operator, leader, dan foreman ke tampilan personal
        if (in_array($user->role, ['operator', 'leader', 'foreman'])) {
            return redirect()->route('jadwal-kerja.operator-index');
        }

        $query = JadwalKerja::with('user');

        // Role tertentu hanya melihat jadwal sendiri
        if (in_array($user->role, ['leader', 'foreman'])) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('tanggal')) {
                $query->byDate($request->tanggal);
            }
            if ($request->filled('shift')) {
                $query->byShift($request->shift);
            }

            // ✅ Filter berdasarkan bagian user (bukan di tabel jadwal)
            if ($request->filled('bagian')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('bagian', $request->bagian);
                });
            }
        }

        $jadwalKerjas = $query->orderBy('tanggal', 'desc')->paginate(10);

        // ✅ Hanya ambil user dari departemen PRODUKSI
        $users = User::where('departemen', 'produksi')
            ->orderBy('nama')
            ->get();

        // ✅ Ambil daftar bagian dari enum database
        $bagianList = ['inject', 'assy'];

        return view('jadwal-kerja.index', compact('jadwalKerjas', 'users'));
    }

    /** 👤 Tampilan khusus untuk operator */
    public function operatorIndex(Request $request)
    {
        $user = Auth::user();

        // Pastikan hanya operator, leader, dan foreman yang bisa akses tampilan ini
        if (!in_array($user->role, ['operator', 'leader', 'foreman'])) {
            return redirect()->route('jadwal-kerja.index');
        }

        // Ambil parameter filter
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $minggu = $request->get('minggu');
        $hari = $request->get('hari');
        $shift = $request->get('shift');

        // Data jadwal operator dengan relasi user
        $jadwalHariIni = JadwalKerja::with('user')
            ->where('user_id', $user->id)
            ->where('tanggal', now()->toDateString())
            ->first();

        // Query untuk jadwal dengan filter
        $query = JadwalKerja::with('user')
            ->where('user_id', $user->id);

        // Terapkan filter
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        if ($hari) {
            $query->whereRaw('DAYOFWEEK(tanggal) = ?', [$hari + 1]); // MySQL DAYOFWEEK dimulai dari 1 (Minggu)
        }

        if ($shift) {
            $query->where('shift', $shift);
        }

        if ($minggu) {
            // Hitung tanggal awal dan akhir minggu berdasarkan bulan dan tahun
            $year = $tahun ?: now()->year;
            $month = $bulan ?: now()->month;

            $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            // Hitung minggu ke-n dalam bulan
            $weekStart = $startOfMonth->copy()->addWeeks($minggu - 1)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            // Pastikan tidak melebihi akhir bulan
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            // Pastikan tidak sebelum awal bulan
            if ($weekStart->lt($startOfMonth)) {
                $weekStart = $startOfMonth;
            }

            $query->whereBetween('tanggal', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        // Terapkan query dan ambil data
        $filteredJadwal = $query->orderBy('tanggal')->get();

        // Jika tidak ada filter, tampilkan jadwal minggu ini dan bulan ini
        if (!$bulan && !$tahun && !$minggu && !$hari && !$shift) {
            // Jadwal minggu ini (7 hari ke depan)
            $jadwalMingguIni = JadwalKerja::with('user')
                ->where('user_id', $user->id)
                ->whereBetween('tanggal', [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString()
                ])
                ->orderBy('tanggal')
                ->get();

            // Jadwal bulan ini
            $jadwalBulanIni = JadwalKerja::with('user')
                ->where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->orderBy('tanggal')
                ->get();
        } else {
            // Jika ada filter, gunakan data yang sudah difilter
            $jadwalMingguIni = $filteredJadwal;
            $jadwalBulanIni = $filteredJadwal;
        }

        // Statistik berdasarkan data yang ada
        $stats = [
            'total_hari_kerja' => $jadwalBulanIni->where('shift', '!=', 'off')->count(),
            'shift_pagi' => $jadwalBulanIni->where('shift', 'pagi')->count(),
            'shift_siang' => $jadwalBulanIni->where('shift', 'siang')->count(),
            'shift_malam' => $jadwalBulanIni->where('shift', 'malam')->count(),
            'hari_libur' => $jadwalBulanIni->where('shift', 'off')->count(),
            'bagian' => $user->bagian ?? 'Tidak ditentukan'
        ];

        // Status hari ini
        $statusHariIni = $this->getStatusHariIni($jadwalHariIni);

        return view('jadwal-kerja.operator-index', compact(
            'user',
            'jadwalHariIni',
            'jadwalMingguIni',
            'jadwalBulanIni',
            'stats',
            'statusHariIni'
        ));
    }

    /** 📊 Helper untuk status hari ini */
    private function getStatusHariIni($jadwalHariIni)
    {
        if (!$jadwalHariIni) {
            return [
                'status' => 'tidak_ada_jadwal',
                'message' => 'Tidak ada jadwal hari ini',
                'color' => 'secondary'
            ];
        }

        if ($jadwalHariIni->shift === 'off') {
            return [
                'status' => 'libur',
                'message' => 'Hari libur',
                'color' => 'info'
            ];
        }

        $now = now();
        $jamMasuk = $jadwalHariIni->jam_masuk ?
            \Carbon\Carbon::createFromFormat('H:i:s', $jadwalHariIni->jam_masuk) : null;
        $jamKeluar = $jadwalHariIni->jam_keluar ?
            \Carbon\Carbon::createFromFormat('H:i:s', $jadwalHariIni->jam_keluar) : null;

        if ($jamMasuk && $jamKeluar) {
            if ($now->between($jamMasuk, $jamKeluar)) {
                return [
                    'status' => 'sedang_kerja',
                    'message' => 'Sedang bekerja',
                    'color' => 'success'
                ];
            } elseif ($now->lt($jamMasuk)) {
                $diff = $now->diffInMinutes($jamMasuk);
                return [
                    'status' => 'akan_masuk',
                    'message' => "Masuk dalam {$diff} menit",
                    'color' => 'warning'
                ];
            } else {
                return [
                    'status' => 'selesai_kerja',
                    'message' => 'Selesai bekerja',
                    'color' => 'primary'
                ];
            }
        }

        return [
            'status' => 'jadwal_tidak_lengkap',
            'message' => 'Jadwal tidak lengkap',
            'color' => 'danger'
        ];
    }

    /** 👁️ Detail Jadwal */
    public function show($id)
    {
        $jadwal = JadwalKerja::with('user')->findOrFail($id);
        return view('jadwal-kerja.show', compact('jadwal'));
    }

    /** 📅 Get shift for user on specific date (AJAX) */
    public function getUserShift(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'nullable|date'
        ]);

        $userId = $request->user_id;
        $tanggal = $request->tanggal ?: now()->toDateString();

        // Ambil jadwal kerja user untuk tanggal tersebut
        $jadwal = JadwalKerja::where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'aktif')
            ->first();

        if (!$jadwal) {
            // Jika tidak ada jadwal, coba ambil dari grup
            $user = User::with('grup')->find($userId);
            $grupShift = $user && $user->grup ? $user->grup->shift : null;

            if ($grupShift && $grupShift !== 'all') {
                // Mapping shift grup ke format checksheet
                $shiftMap = [
                    'pagi' => 'shift_1',
                    'siang' => 'shift_2',
                    'malam' => 'shift_3'
                ];

                return response()->json([
                    'success' => true,
                    'shift' => $shiftMap[$grupShift] ?? null,
                    'shift_display' => ucfirst($grupShift),
                    'source' => 'grup'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal kerja untuk user ini pada tanggal tersebut',
                'shift' => null,
                'shift_display' => null
            ]);
        }

        // Mapping shift jadwal kerja ke format checksheet
        $shiftMap = [
            'pagi' => 'shift_1',
            'siang' => 'shift_2',
            'malam' => 'shift_3',
            'off' => null
        ];

        $shift = $shiftMap[$jadwal->shift] ?? null;
        $shiftDisplay = $jadwal->shift === 'off' ? 'Libur' : 'Shift ' . ucfirst($jadwal->shift);

        return response()->json([
            'success' => true,
            'shift' => $shift,
            'shift_display' => $shiftDisplay,
            'source' => 'jadwal_kerja'
        ]);
    }

    /** ➕ Simpan jadwal baru (AJAX) */
    public function store(Request $request)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambah jadwal kerja.'
            ], 403);
        }

        try {
            // Validasi dengan format yang lebih fleksibel
            $validated = $request->validate([
                'user_id'    => 'required|exists:users,id',
                'tanggal'    => 'required|date',
                'shift'      => 'required|in:pagi,siang,malam,off',
                'status'     => 'required|in:aktif,tidak_aktif',
                'jam_masuk'  => 'nullable|string',
                'jam_keluar' => 'nullable|string',
                'keterangan' => 'nullable|string|max:500',
            ]);

            // Validasi manual untuk format waktu (H:i atau HH:mm)
            if (!empty($validated['jam_masuk'])) {
                if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam_masuk'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_masuk' => ['Format jam masuk harus H:i (contoh: 7:00 atau 07:00)']
                    ]);
                }
                // Normalisasi format waktu ke H:i (tanpa leading zero untuk jam < 10)
                $parts = explode(':', $validated['jam_masuk']);
                $validated['jam_masuk'] = (int)$parts[0] . ':' . $parts[1];
            }
            if (!empty($validated['jam_keluar'])) {
                if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam_keluar'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => ['Format jam keluar harus H:i (contoh: 7:00 atau 07:00)']
                    ]);
                }
                // Normalisasi format waktu ke H:i (tanpa leading zero untuk jam < 10)
                $parts = explode(':', $validated['jam_keluar']);
                $validated['jam_keluar'] = (int)$parts[0] . ':' . $parts[1];
            }

            $user = User::findOrFail($validated['user_id']);

            // ✅ Set jam default otomatis
            $defaultJam = JadwalKerja::getDefaultJamKerja($validated['shift']);
            $validated['jam_masuk']  = $validated['jam_masuk'] ?? $defaultJam['jam_masuk'];
            $validated['jam_keluar'] = $validated['jam_keluar'] ?? $defaultJam['jam_keluar'];

            // ✅ Simpan created_by
            $validated['created_by'] = Auth::id();

            // ✅ Simpan jadwal tanpa kolom bagian (karena diambil dari relasi user)
            $jadwal = JadwalKerja::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal kerja berhasil ditambahkan',
                'data'    => $jadwal->load('user')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error store jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /** ✏️ Ambil data untuk modal update (AJAX) */
    public function edit($id)
    {
        try {
            $jadwal = JadwalKerja::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $jadwal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /** 🔄 Update jadwal kerja (AJAX) */
    public function update(Request $request, $id)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah jadwal kerja.'
            ], 403);
        }

        try {
            $jadwal = JadwalKerja::findOrFail($id);

            // Validasi dengan format yang lebih fleksibel
            $validated = $request->validate([
                'user_id'    => 'required|exists:users,id',
                'tanggal'    => 'required|date',
                'shift'      => 'required|in:pagi,siang,malam,off',
                'status'     => 'required|in:aktif,tidak_aktif',
                'jam_masuk'  => 'nullable|string',
                'jam_keluar' => 'nullable|string',
                'keterangan' => 'nullable|string|max:500',
            ]);

            // Validasi manual untuk format waktu (H:i atau HH:mm)
            if (!empty($validated['jam_masuk'])) {
                if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam_masuk'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_masuk' => ['Format jam masuk harus H:i (contoh: 7:00 atau 07:00)']
                    ]);
                }
                // Normalisasi format waktu ke H:i (tanpa leading zero untuk jam < 10)
                $parts = explode(':', $validated['jam_masuk']);
                $validated['jam_masuk'] = (int)$parts[0] . ':' . $parts[1];
            }
            if (!empty($validated['jam_keluar'])) {
                if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $validated['jam_keluar'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => ['Format jam keluar harus H:i (contoh: 7:00 atau 07:00)']
                    ]);
                }
                // Normalisasi format waktu ke H:i (tanpa leading zero untuk jam < 10)
                $parts = explode(':', $validated['jam_keluar']);
                $validated['jam_keluar'] = (int)$parts[0] . ':' . $parts[1];
            }

            $defaultJam = JadwalKerja::getDefaultJamKerja($validated['shift']);
            $validated['jam_masuk']  = $validated['jam_masuk'] ?? $defaultJam['jam_masuk'];
            $validated['jam_keluar'] = $validated['jam_keluar'] ?? $defaultJam['jam_keluar'];

            $jadwal->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal kerja berhasil diperbarui',
                'data'    => $jadwal->load('user')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error update jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /** 🗑️ Hapus jadwal kerja (AJAX) */
    public function destroy($id)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus jadwal kerja.'
            ], 403);
        }

        try {
            $jadwal = JadwalKerja::findOrFail($id);
            $jadwal->delete();

            Log::warning("🗑️ Jadwal kerja id={$id} dihapus oleh user=" . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Jadwal kerja berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error delete jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /** 📥 Download Template Excel */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header
            $sheet->setCellValue('A1', 'NIK');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Tanggal');
            $sheet->setCellValue('D1', 'Shift');
            $sheet->setCellValue('E1', 'Status');
            $sheet->setCellValue('F1', 'Keterangan');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Set column width
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(30);

            // Add example data
            $sheet->setCellValue('A2', '1234567890');
            $sheet->setCellValue('B2', 'John Doe');
            $sheet->setCellValue('C2', date('Y-m-d'));
            $sheet->setCellValue('D2', 'pagi');
            $sheet->setCellValue('E2', 'aktif');
            $sheet->setCellValue('F2', 'Contoh keterangan');

            // Add instructions sheet
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Instruksi');
            $instructionSheet->setCellValue('A1', 'INSTRUKSI PENGISIAN TEMPLATE JADWAL KERJA');
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            $instructions = [
                ['', ''],
                ['Kolom', 'Keterangan'],
                ['NIK', 'Nomor Induk Karyawan (harus sudah terdaftar di sistem)'],
                ['Nama', 'Nama karyawan (untuk referensi, sistem akan menggunakan NIK)'],
                ['Tanggal', 'Format: YYYY-MM-DD (contoh: 2025-12-10)'],
                ['Shift', 'Pilihan: pagi, siang, malam, atau off'],
                ['Status', 'Pilihan: aktif atau tidak_aktif'],
                ['Keterangan', 'Catatan tambahan (opsional, maksimal 500 karakter)'],
                ['', ''],
                ['CATATAN PENTING:', ''],
                ['1.', 'Pastikan NIK karyawan sudah terdaftar di sistem'],
                ['2.', 'Format tanggal harus YYYY-MM-DD'],
                ['3.', 'Shift harus salah satu dari: pagi, siang, malam, off'],
                ['4.', 'Status harus salah satu dari: aktif, tidak_aktif'],
                ['5.', 'Hapus baris contoh sebelum mengupload'],
            ];

            $row = 1;
            foreach ($instructions as $instruction) {
                $instructionSheet->setCellValue('A' . $row, $instruction[0]);
                $instructionSheet->setCellValue('B' . $row, $instruction[1]);
                $row++;
            }

            $instructionSheet->getColumnDimension('A')->setWidth(20);
            $instructionSheet->getColumnDimension('B')->setWidth(60);

            // Set active sheet back to data sheet
            $spreadsheet->setActiveSheetIndex(0);

            // Create writer and download
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Template_Jadwal_Kerja_' . date('Y-m-d') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('❌ Error download template: ' . $e->getMessage());
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /** 📤 Import Excel */
    public function importExcel(Request $request)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengimport jadwal kerja.');
        }

        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            array_shift($rows);

            $imported = 0;
            $errors = [];
            $skipped = 0;

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 karena index mulai dari 0 dan ada header

                // Skip empty rows
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                try {
                    $nik = $row[0];
                    $tanggal = $row[2];
                    $shift = strtolower(trim($row[3]));
                    $status = strtolower(trim($row[4]));
                    $keterangan = $row[5] ?? null;

                    // Validate required fields
                    if (empty($nik) || empty($tanggal) || empty($shift) || empty($status)) {
                        $errors[] = "Baris {$rowNumber}: Data tidak lengkap";
                        continue;
                    }

                    // Find user by NIK
                    $user = User::where('nik', $nik)->first();
                    if (!$user) {
                        $errors[] = "Baris {$rowNumber}: NIK {$nik} tidak ditemukan";
                        continue;
                    }

                    // Validate shift
                    if (!in_array($shift, ['pagi', 'siang', 'malam', 'off'])) {
                        $errors[] = "Baris {$rowNumber}: Shift tidak valid (harus: pagi, siang, malam, atau off)";
                        continue;
                    }

                    // Validate status
                    if (!in_array($status, ['aktif', 'tidak_aktif'])) {
                        $errors[] = "Baris {$rowNumber}: Status tidak valid (harus: aktif atau tidak_aktif)";
                        continue;
                    }

                    // Parse tanggal
                    try {
                        if (is_numeric($tanggal)) {
                            // Excel date format
                            $tanggalParsed = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                        } else {
                            // String date format
                            $tanggalParsed = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$rowNumber}: Format tanggal tidak valid";
                        continue;
                    }

                    // Get default jam kerja
                    $defaultJam = JadwalKerja::getDefaultJamKerja($shift);

                    // Create jadwal
                    JadwalKerja::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggalParsed,
                        'shift' => $shift,
                        'status' => $status,
                        'jam_masuk' => $defaultJam['jam_masuk'],
                        'jam_keluar' => $defaultJam['jam_keluar'],
                        'keterangan' => $keterangan,
                        'created_by' => Auth::id(),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            $message = "Berhasil mengimport {$imported} jadwal kerja";
            if ($skipped > 0) {
                $message .= ", {$skipped} baris kosong dilewati";
            }
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " baris gagal diimport";
                Log::warning('Import errors: ' . implode('; ', $errors));
            }

            if ($imported > 0) {
                return back()->with('success', $message);
            } else {
                return back()->with('error', 'Tidak ada data yang berhasil diimport. ' . implode('; ', array_slice($errors, 0, 5)));
            }
        } catch (\Exception $e) {
            Log::error('❌ Error import excel: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }
}

