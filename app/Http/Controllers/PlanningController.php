<?php

namespace App\Http\Controllers;

use App\Models\PlanningProduksi;
use App\Models\PlanningProduksiJam;
use App\Models\Moldshop;
use App\Models\Mesin;
use App\Models\Part;
use App\Models\JadwalKerja;
use App\Models\Meja; // Added Meja model
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory; // Added
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Added
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Added

class PlanningController extends Controller
{
    /**
     * Tampilkan semua planning produksi
     */
    public function index(Request $request)
    {
        // Default filter: hanya planning hari ini
        $filterTanggal = $request->get('tanggal', date('Y-m-d'));

        // Query planning dengan relasi
        $query = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'meja',
            'part',
            'jadwalKerja',
            'jam',
            'approver'
        ]);

        // Jika ada filter tanggal, tambahkan where clause
        if ($filterTanggal) {
            $query->whereDate('tanggal', $filterTanggal);
        }

        $plannings = $query->latest('tanggal')->paginate(10);

        $moldshops = Moldshop::all();
        $mesins    = Mesin::all();
        $mejas     = \App\Models\Meja::all();
        $parts     = Part::all();
        $jadwals   = JadwalKerja::all();

        return view('planning.index', compact('plannings', 'moldshops', 'mesins', 'mejas', 'parts', 'jadwals', 'filterTanggal'));
    }

    /**
     * Tampilkan riwayat planning produksi (kemarin atau berdasarkan tanggal)
     */
    public function history(Request $request)
    {
        // Default filter: planning kemarin
        $filterTanggal = $request->get('tanggal', Carbon::yesterday()->format('Y-m-d'));

        // Query planning dengan relasi
        $query = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'part',
            'jadwalKerja',
            'jam'
        ]);

        // Filter berdasarkan tanggal
        $query->whereDate('tanggal', $filterTanggal);

        $plannings = $query->latest('tanggal')->paginate(20);

        return view('planning.history', compact('plannings', 'filterTanggal'));
    }

    /**
     * Tampilkan detail planning produksi (untuk modal detail)
     */
    public function show($id)
    {
        $planning = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'meja',
            'part',
            'jadwalKerja',
            'jam'
        ])->findOrFail($id);

        // Ambil data per jam dari tabel planning_produksi_jam
        $dataPerJam = $this->getHourData($planning->id);

        return response()->json(array_merge([
            'id'         => $planning->id,
            'bagian'     => $planning->bagian, // Ambil langsung dari planning
            'no_mold'    => $planning->moldshop->no_mold ?? '-',
            'mesin'      => $planning->mesin->nama_mesin ?? '-',
            'meja'       => $planning->meja->nama_meja ?? '-',
            'tanggal'    => $planning->tanggal,
            'lot'        => $planning->lot_produksi,
            'no_part'    => $planning->part->no_parts ?? '-',
            'nama_part'  => $planning->part->nama_part ?? '-',
            'type'       => $planning->part->type ?? '-',
            'qty'        => $planning->qty_planning_produksi,
            'status'     => $planning->status ?? '-',
            'status_approval' => $planning->status_approval ?? 'pending',
            'catatan'    => $planning->catatan ?? '-',
            'shift'      => $planning->jadwalKerja->shift ?? '-',
            'jam_masuk'  => $planning->jadwalKerja->jam_masuk ?? '-',
            'jam_keluar' => $planning->jadwalKerja->jam_keluar ?? '-',
            'qty_shift'  => $planning->qty_shift ?? '-',
            'keterangan' => $planning->jadwalKerja->keterangan ?? '-',
            'target_shift_pagi' => $planning->target_shift_pagi ?? 0,
            'target_shift_siang' => $planning->target_shift_siang ?? 0,
            'target_shift_malam' => $planning->target_shift_malam ?? 0,
            'total_shift' => $planning->total_shift ?? 0,
        ], $dataPerJam));
    }

    /**
     * Simpan planning baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bagian'      => 'required|in:inject,assy',
            'moldshop_id' => 'required_if:bagian,inject|nullable|exists:moldshops,id',
            'mesin_id'    => 'required_if:bagian,inject|nullable|exists:mesin,id',
            'meja_id'     => 'required_if:bagian,assy|nullable|exists:meja,id_meja',
            'part_id'     => 'required|exists:parts,id',
            'tanggal'     => 'required|date',
            'lot_produksi' => 'required|string|max:255',
            'qty_planning_produksi' => 'required|integer|min:1',

            // per jam (boleh nullable biar fleksibel)
            'pagi_jam_07' => 'nullable|integer|min:0',
            'pagi_jam_08' => 'nullable|integer|min:0',
            'pagi_jam_09' => 'nullable|integer|min:0',
            'pagi_jam_10' => 'nullable|integer|min:0',
            'pagi_jam_11' => 'nullable|integer|min:0',
            'pagi_jam_12' => 'nullable|integer|min:0',
            'pagi_jam_13' => 'nullable|integer|min:0',
            'pagi_jam_14' => 'nullable|integer|min:0',

            'siang_jam_15' => 'nullable|integer|min:0',
            'siang_jam_16' => 'nullable|integer|min:0',
            'siang_jam_17' => 'nullable|integer|min:0',
            'siang_jam_18' => 'nullable|integer|min:0',
            'siang_jam_19' => 'nullable|integer|min:0',
            'siang_jam_20' => 'nullable|integer|min:0',
            'siang_jam_21' => 'nullable|integer|min:0',
            'siang_jam_22' => 'nullable|integer|min:0',

            'malam_jam_23' => 'nullable|integer|min:0',
            'malam_jam_00' => 'nullable|integer|min:0',
            'malam_jam_01' => 'nullable|integer|min:0',
            'malam_jam_02' => 'nullable|integer|min:0',
            'malam_jam_03' => 'nullable|integer|min:0',
            'malam_jam_04' => 'nullable|integer|min:0',
            'malam_jam_05' => 'nullable|integer|min:0',
            'malam_jam_06' => 'nullable|integer|min:0',

            'target_shift_pagi' => 'nullable|integer|min:0',
            'target_shift_siang' => 'nullable|integer|min:0',
            'target_shift_malam' => 'nullable|integer|min:0',

            'catatan' => 'nullable|string',
        ]);

        // Hitung total_shift otomatis
        $validated['total_shift'] = ($validated['target_shift_pagi'] ?? 0) +
            ($validated['target_shift_siang'] ?? 0) +
            ($validated['target_shift_malam'] ?? 0);

        // Validasi: Total jam tidak boleh melebihi target shift
        $totalPagi = 0;
        for ($i = 7; $i <= 14; $i++) {
            $totalPagi += $validated['pagi_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalPagi > ($validated['target_shift_pagi'] ?? 0)) {
            return back()->withErrors(['target_shift_pagi' => "Total jam shift pagi ($totalPagi) melebihi target shift pagi (" . ($validated['target_shift_pagi'] ?? 0) . ")."])->withInput();
        }

        $totalSiang = 0;
        for ($i = 15; $i <= 22; $i++) {
            $totalSiang += $validated['siang_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalSiang > ($validated['target_shift_siang'] ?? 0)) {
            return back()->withErrors(['target_shift_siang' => "Total jam shift siang ($totalSiang) melebihi target shift siang (" . ($validated['target_shift_siang'] ?? 0) . ")."])->withInput();
        }

        $totalMalam = 0;
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $totalMalam += $validated['malam_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalMalam > ($validated['target_shift_malam'] ?? 0)) {
            return back()->withErrors(['target_shift_malam' => "Total jam shift malam ($totalMalam) melebihi target shift malam (" . ($validated['target_shift_malam'] ?? 0) . ")."])->withInput();
        }

        // Pastikan field yang tidak digunakan di-set ke null
        if ($validated['bagian'] === 'inject') {
            $validated['meja_id'] = null;
        } else if ($validated['bagian'] === 'assy') {
            $validated['moldshop_id'] = null;
            $validated['mesin_id'] = null;
        }

        // Simpan planning utama
        $planning = PlanningProduksi::create($validated);

        // Simpan data per jam ke tabel planning_produksi_jam
        $this->saveHourData($planning->id, $validated);

        // Generate digital signature QR code
        $this->generateDigitalSignature($planning);

        return redirect()->route('planning.index')
            ->with('success', 'Planning produksi berhasil dibuat.');
    }


    /**
     * Update planning
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'bagian'      => 'required|in:inject,assy',
            'moldshop_id' => 'required_if:bagian,inject|nullable|exists:moldshops,id',
            'mesin_id'    => 'required_if:bagian,inject|nullable|exists:mesin,id',
            'meja_id'     => 'required_if:bagian,assy|nullable|exists:meja,id_meja',
            'part_id'     => 'required|exists:parts,id',
            'tanggal'     => 'required|date',
            'lot_produksi' => 'required|string|max:255',
            'qty_planning_produksi' => 'required|integer|min:0',

            // per jam (boleh nullable biar fleksibel)
            'pagi_jam_07' => 'nullable|integer|min:0',
            'pagi_jam_08' => 'nullable|integer|min:0',
            'pagi_jam_09' => 'nullable|integer|min:0',
            'pagi_jam_10' => 'nullable|integer|min:0',
            'pagi_jam_11' => 'nullable|integer|min:0',
            'pagi_jam_12' => 'nullable|integer|min:0',
            'pagi_jam_13' => 'nullable|integer|min:0',
            'pagi_jam_14' => 'nullable|integer|min:0',

            'siang_jam_15' => 'nullable|integer|min:0',
            'siang_jam_16' => 'nullable|integer|min:0',
            'siang_jam_17' => 'nullable|integer|min:0',
            'siang_jam_18' => 'nullable|integer|min:0',
            'siang_jam_19' => 'nullable|integer|min:0',
            'siang_jam_20' => 'nullable|integer|min:0',
            'siang_jam_21' => 'nullable|integer|min:0',
            'siang_jam_22' => 'nullable|integer|min:0',

            'malam_jam_23' => 'nullable|integer|min:0',
            'malam_jam_00' => 'nullable|integer|min:0',
            'malam_jam_01' => 'nullable|integer|min:0',
            'malam_jam_02' => 'nullable|integer|min:0',
            'malam_jam_03' => 'nullable|integer|min:0',
            'malam_jam_04' => 'nullable|integer|min:0',
            'malam_jam_05' => 'nullable|integer|min:0',
            'malam_jam_06' => 'nullable|integer|min:0',

            'target_shift_pagi' => 'nullable|integer|min:0',
            'target_shift_siang' => 'nullable|integer|min:0',
            'target_shift_malam' => 'nullable|integer|min:0',

            'catatan' => 'nullable|string',

        ]);

        // Hitung total_shift otomatis
        $validated['total_shift'] = ($validated['target_shift_pagi'] ?? 0) +
            ($validated['target_shift_siang'] ?? 0) +
            ($validated['target_shift_malam'] ?? 0);

        // Validasi: Total jam tidak boleh melebihi target shift
        $totalPagi = 0;
        for ($i = 7; $i <= 14; $i++) {
            $totalPagi += $validated['pagi_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalPagi > ($validated['target_shift_pagi'] ?? 0)) {
            return back()->withErrors(['target_shift_pagi' => "Total jam shift pagi ($totalPagi) melebihi target shift pagi (" . ($validated['target_shift_pagi'] ?? 0) . ")."])->withInput();
        }

        $totalSiang = 0;
        for ($i = 15; $i <= 22; $i++) {
            $totalSiang += $validated['siang_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalSiang > ($validated['target_shift_siang'] ?? 0)) {
            return back()->withErrors(['target_shift_siang' => "Total jam shift siang ($totalSiang) melebihi target shift siang (" . ($validated['target_shift_siang'] ?? 0) . ")."])->withInput();
        }

        $totalMalam = 0;
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $totalMalam += $validated['malam_jam_' . sprintf('%02d', $i)] ?? 0;
        }
        if ($totalMalam > ($validated['target_shift_malam'] ?? 0)) {
            return back()->withErrors(['target_shift_malam' => "Total jam shift malam ($totalMalam) melebihi target shift malam (" . ($validated['target_shift_malam'] ?? 0) . ")."])->withInput();
        }

        // Pastikan field yang tidak digunakan di-set ke null
        if ($validated['bagian'] === 'inject') {
            $validated['meja_id'] = null;
        } else if ($validated['bagian'] === 'assy') {
            $validated['moldshop_id'] = null;
            $validated['mesin_id'] = null;
        }

        $planning = PlanningProduksi::findOrFail($id);
        $planning->update($validated);

        // Update data per jam ke tabel planning_produksi_jam
        $this->updateHourData($planning->id, $validated);

        // Generate digital signature QR code (update signature)
        $this->generateDigitalSignature($planning);

        return redirect()->route('planning.index')
            ->with('success', 'Planning produksi berhasil diperbarui.');
    }

    /**
     * Hapus planning
     */
    public function destroy($id)
    {
        $planning = PlanningProduksi::findOrFail($id);
        $planning->delete();

        return redirect()->route('planning.index')
            ->with('success', 'Planning produksi berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        // Filter tanggal - default: cetak planning untuk BESOK (karena biasanya planning dibuat hari ini untuk esok)
        // Jika mau cetak planning hari ini, pass parameter ?tanggal=hari_ini
        $filterTanggal = $request->get('tanggal', 'besok');
        $filterBagian = $request->get('bagian', 'all'); // Parameter baru untuk filter bagian

        if ($filterTanggal === 'besok') {
            // Planning untuk besok (planning yang dibuat hari ini)
            $tanggalFilter = Carbon::tomorrow()->format('Y-m-d');
            $filterType = 'besok';
        } elseif ($filterTanggal === 'hari_ini') {
            // Planning untuk hari ini
            $tanggalFilter = now()->format('Y-m-d');
            $filterType = 'hari_ini';
        } else {
            // Tanggal spesifik dari parameter
            $tanggalFilter = $filterTanggal;
            $filterType = 'spesifik';
        }

        // Log untuk debugging
        Log::info('Cetak Planning Request', [
            'filterTanggal' => $filterTanggal,
            'tanggalFilter' => $tanggalFilter,
            'filterBagian' => $filterBagian,
            'filterType' => $filterType
        ]);

        // Query planning berdasarkan filter bagian
        // Jika filter bagian = 'inject', hanya ambil inject
        // Jika filter bagian = 'assy', hanya ambil assy
        // Jika filter bagian = 'all', ambil keduanya

        $planningsInject = collect(); // Default empty collection
        $planningsAssy = collect(); // Default empty collection

        if ($filterBagian === 'all' || $filterBagian === 'inject') {
            $queryInject = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam', 'approver'])
                ->where('bagian', 'inject')
                ->whereDate('tanggal', $tanggalFilter);

            $planningsInject = $queryInject->orderBy('moldshop_id')
                ->orderBy('tanggal')
                ->get();

            Log::info('Query Inject Result', [
                'count' => $planningsInject->count(),
                'tanggal' => $tanggalFilter
            ]);
        }

        if ($filterBagian === 'all' || $filterBagian === 'assy') {
            $queryAssy = PlanningProduksi::with(['meja', 'part', 'jam', 'approver'])
                ->where('bagian', 'assy')
                ->whereDate('tanggal', $tanggalFilter);

            $planningsAssy = $queryAssy->orderBy('meja_id')
                ->orderBy('tanggal')
                ->get();

            Log::info('Query Assy Result', [
                'count' => $planningsAssy->count(),
                'tanggal' => $tanggalFilter
            ]);
        }

        // Info creator (Pak Diar - ini bisa diambil dari user yang membuat atau hardcode untuk sementara)
        $creatorName = 'Diar W';
        $creatorDate = now()->format('d-m-Y');

        // Return view untuk cetak
        return view('planning.cetak', compact('planningsInject', 'planningsAssy', 'creatorName', 'creatorDate', 'tanggalFilter', 'filterType', 'filterBagian'));
    }

    /**
     * Cetak planning berdasarkan tanggal tertentu
     */
    public function cetakByTanggal(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $plannings = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('moldshop_id')
            ->orderBy('tanggal')
            ->get();

        $creatorName = 'Diar W';
        $creatorDate = now()->format('d-m-Y');

        return view('planning.cetak', compact('plannings', 'tanggal', 'creatorName', 'creatorDate'));
    }

    /**
     * Cetak planning berdasarkan range tanggal
     */
    public function cetakByRange(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-d'));

        $plannings = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('moldshop_id')
            ->orderBy('tanggal')
            ->get();

        $creatorName = 'Diar W';
        $creatorDate = now()->format('d-m-Y');

        return view('planning.cetak', compact('plannings', 'tanggalMulai', 'tanggalSelesai', 'creatorName', 'creatorDate'));
    }

    /**
     * Helper method untuk menyimpan data per jam
     */
    private function saveHourData($planningId, $validated)
    {
        // Hapus data jam lama jika ada
        PlanningProduksiJam::where('planning_id', $planningId)->delete();

        // SHIFT 1 (PAGI) - 07:00 sampai 14:00
        for ($i = 7; $i <= 14; $i++) {
            $key = 'pagi_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'pagi',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }

        // SHIFT 2 (SIANG) - 15:00 sampai 22:00
        for ($i = 15; $i <= 22; $i++) {
            $key = 'siang_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'siang',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }

        // SHIFT 3 (MALAM) - 23:00, 00:00 sampai 06:00
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $key = 'malam_jam_' . sprintf('%02d', $i);
            if (isset($validated[$key]) && $validated[$key] > 0) {
                PlanningProduksiJam::create([
                    'planning_id' => $planningId,
                    'shift' => 'malam',
                    'jam' => sprintf('%02d:00:00', $i),
                    'target' => $validated[$key]
                ]);
            }
        }
    }

    /**
     * Helper method untuk mengupdate data per jam
     */
    private function updateHourData($planningId, $validated)
    {
        $this->saveHourData($planningId, $validated);
    }

    /**
     * Helper method untuk mengambil data per jam
     */
    private function getHourData($planningId)
    {
        $hourData = PlanningProduksiJam::where('planning_id', $planningId)->get();
        $dataPerJam = [];

        // Inisialisasi semua jam dengan nilai 0
        // SHIFT 1 (PAGI) - 07:00 sampai 14:00
        for ($i = 7; $i <= 14; $i++) {
            $dataPerJam["pagi_jam_" . sprintf('%02d', $i)] = 0;
        }

        // SHIFT 2 (SIANG) - 15:00 sampai 22:00
        for ($i = 15; $i <= 22; $i++) {
            $dataPerJam["siang_jam_" . sprintf('%02d', $i)] = 0;
        }

        // SHIFT 3 (MALAM) - 23:00, 00:00 sampai 06:00
        foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
            $dataPerJam["malam_jam_" . sprintf('%02d', $i)] = 0;
        }

        // Isi dengan data dari database
        foreach ($hourData as $data) {
            $hour = date('H', strtotime($data->jam));
            $shift = $data->shift;

            if ($shift == 'pagi') {
                $dataPerJam["pagi_jam_" . sprintf('%02d', $hour)] = $data->target;
            } elseif ($shift == 'siang') {
                $dataPerJam["siang_jam_" . sprintf('%02d', $hour)] = $data->target;
            } elseif ($shift == 'malam') {
                $dataPerJam["malam_jam_" . sprintf('%02d', $hour)] = $data->target;
            }
        }

        return $dataPerJam;
    }

    /**
     * Generate digital signature QR code untuk planning
     */
    private function generateDigitalSignature(PlanningProduksi $planning)
    {
        try {
            // QR code akan berisi URL langsung ke TTD.png
            $ttdUrl = asset('images/TTD.png');
            $qrContent = $ttdUrl; // QR code berisi URL langsung ke TTD.png

            // Generate QR code dengan data Pak Diar
            $writer = new PngWriter();
            $builder = new Builder($writer);

            $result = $builder->build(
                data: $qrContent,
                size: 200,
                margin: 5
            );

            // Save QR code to storage
            $filename = 'planning_signatures/' . $planning->id . '_ttd_' . time() . '.png';
            $path = 'public/' . $filename;

            if (!Storage::exists('public/planning_signatures')) {
                Storage::makeDirectory('public/planning_signatures');
            }

            // Get binary data
            $qrData = $result->getString();

            Storage::put($path, $qrData);

            // Update planning dengan signature info
            $planning->update([
                'signature_qr_code' => $filename,
                'signed_by' => 'Diar W', // Selalu Pak Diar sebagai pembuat planning
                'signed_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate digital signature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Halaman approval untuk Kabag PPIC
     */
    public function approvalPage(Request $request)
    {
        // Cek apakah user adalah super_admin atau kabag PPIC
        $user = Auth::user();
        $allowedDepartemen = ['PPIC', 'Production Planning Inventory Control'];
        if ($user->role !== 'super_admin' && ($user->role !== 'kabag' || !in_array($user->departemen, $allowedDepartemen))) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        // Filter tanggal (default: besok)
        $filterTanggal = $request->get('tanggal', Carbon::tomorrow()->format('Y-m-d'));

        // Query planning yang pending approval
        $query = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'part',
            'jadwalKerja',
            'jam'
        ])->where('status_approval', 'pending');

        // Filter berdasarkan tanggal
        if ($filterTanggal) {
            $query->whereDate('tanggal', $filterTanggal);
        }

        $plannings = $query->latest('tanggal')->paginate(15);

        return view('planning.approval', compact('plannings', 'filterTanggal'));
    }

    /**
     * Approval planning oleh Kabag PPIC
     */
    public function approval(Request $request)
    {
        $request->validate([
            'planning_id' => 'required|exists:planning_produksi,id',
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject'
        ]);

        // Cek apakah user adalah super_admin atau kabag PPIC
        $user = Auth::user();
        $allowedDepartemen = ['PPIC', 'Production Planning Inventory Control'];
        if ($user->role !== 'super_admin' && ($user->role !== 'kabag' || !in_array($user->departemen, $allowedDepartemen))) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan approval.'
            ], 403);
        }

        $planning = PlanningProduksi::findOrFail($request->planning_id);

        // Cek apakah planning sudah di-approve/reject
        if ($planning->status_approval !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Planning ini sudah di-' . ($planning->status_approval == 'approved' ? 'setujui' : 'tolak') . '.'
            ], 400);
        }

        // Update status approval
        if ($request->action == 'approve') {
            // Generate QR code untuk approval
            $qrCodeFilename = $this->generateApprovalQrCode($planning, $user);

            $planning->update([
                'status_approval' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'approval_qr_code' => $qrCodeFilename // Simpan di kolom approval_qr_code, bukan signature_qr_code
            ]);

            $message = 'Planning berhasil disetujui dan QR code telah dibuat.';
        } else {
            $planning->update([
                'status_approval' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);

            $message = 'Planning berhasil ditolak.';
        }

        $response = [
            'success' => true,
            'message' => $message
        ];

        return response()->json($response);
    }

    /**
     * Generate QR code untuk approval oleh Kabag PPIC
     * Format sama seperti QR code Diar (URL langsung ke gambar tanda tangan)
     */
    private function generateApprovalQrCode(PlanningProduksi $planning, $approver)
    {
        try {
            // QR code akan berisi URL langsung ke TTD.png (sama seperti Diar)
            // Jika nanti ada gambar TTD khusus untuk Ariyanto, bisa diganti ke asset('images/TTD_Ariyanto.png')
            $ttdUrl = asset('images/TTD.png');
            $qrContent = $ttdUrl; // QR code berisi URL langsung ke TTD.png

            // Generate QR code dengan format yang sama seperti Diar
            $writer = new PngWriter();
            $builder = new Builder($writer);

            $result = $builder->build(
                data: $qrContent,
                size: 200,
                margin: 5
            );

            // Save QR code to storage
            $filename = 'planning_approvals/' . $planning->id . '_approved_' . time() . '.png';
            $path = 'public/' . $filename;

            if (!Storage::exists('public/planning_approvals')) {
                Storage::makeDirectory('public/planning_approvals');
            }

            // Get binary data
            $qrDataBinary = $result->getString();
            Storage::put($path, $qrDataBinary);

            Log::info('Approval QR code generated', [
                'planning_id' => $planning->id,
                'approver' => $approver->nama ?? 'Ariyanto',
                'filename' => $filename,
                'format' => 'URL_TTD'
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to generate approval QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Secure route untuk mengakses QR code planning
     * type: 'signature' untuk QR code Diar (creator) atau 'approval' untuk QR code Ariyanto (approver)
     */
    public function secureQrCode($id, $type = 'signature')
    {
        try {
            $planning = PlanningProduksi::findOrFail($id);

            // Tentukan file QR code berdasarkan type
            if ($type === 'approval') {
                $qrCodePath = $planning->approval_qr_code;
            } else {
                $qrCodePath = $planning->signature_qr_code;
            }

            // Jika QR code tidak ada, return 404
            if (!$qrCodePath) {
                abort(404, 'QR Code tidak ditemukan');
            }

            // Validasi path untuk keamanan (mencegah directory traversal)
            $fullPath = storage_path('app/public/' . $qrCodePath);
            $realPath = realpath($fullPath);

            // Pastikan file ada dan dalam direktori yang benar
            if (!$realPath || !file_exists($realPath)) {
                abort(404, 'QR Code tidak ditemukan');
            }

            // Pastikan file dalam direktori storage yang benar
            $storagePath = realpath(storage_path('app/public'));
            if (strpos($realPath, $storagePath) !== 0) {
                abort(403, 'Akses tidak diizinkan');
            }

            // Return file dengan header yang tepat
            return response()->file($realPath, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=3600'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to access QR code: ' . $e->getMessage());
            abort(404, 'QR Code tidak ditemukan');
        }
    }

    // --- Excel Import/Export Functions ---

    /**
     * Download Template Excel
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
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
                // Hourly breakdown columns start at O
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

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ];
            $sheet->getStyle('A1:AL1')->applyFromArray($headerStyle);

            // Column widths
            $columnWidths = ['A' => 15, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 20, 'F' => 20, 'G' => 30, 'H' => 15, 'I' => 20, 'J' => 15, 'K' => 15, 'L' => 15, 'M' => 15, 'N' => 30];
            foreach ($columnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            // Auto-size hourly columns slightly
            foreach (range('O', 'Z') as $col) $sheet->getColumnDimension($col)->setWidth(10);
            foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL'] as $col) $sheet->getColumnDimension($col)->setWidth(10);

            // Example Data
            $row = 2;
            $sheet->setCellValue('A' . $row, 'inject');
            $sheet->setCellValue('B' . $row, date('Y-m-d', strtotime('+1 day')));
            $sheet->setCellValue('C' . $row, 'Mesin 10');
            $sheet->setCellValue('D' . $row, 'MOLD-001');
            $sheet->setCellValue('E' . $row, 'PART-12345');
            $sheet->setCellValue('F' . $row, 'BRG-123');
            $sheet->setCellValue('G' . $row, 'Nama Part Contoh');
            $sheet->setCellValue('H' . $row, 'Type A');
            $sheet->setCellValue('I' . $row, 'LOT-ABC-123');
            $sheet->setCellValue('J' . $row, 1200);
            $sheet->setCellValue('K' . $row, 400); // Target Pagi
            $sheet->setCellValue('L' . $row, 400); // Target Siang
            $sheet->setCellValue('M' . $row, 400); // Target Malam
            $sheet->setCellValue('N' . $row, 'Contoh import');

            // Example hourly distribution (50 per hour)
            $cols = ['O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL'];
            foreach ($cols as $col) {
                $sheet->setCellValue($col . $row, 50);
            }

            // Writer
            $writer = new Xlsx($spreadsheet);
            $filename = 'Template_Planning_Produksi_Lengkap.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate template.');
        }
    }

    /**
     * Import Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove Header
            array_shift($rows);

            $successCount = 0;
            $errorRows = [];

            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $rowNum = $index + 2;

                // Parse Base Data
                $bagian = strtolower(trim($row[0] ?? ''));
                $tanggal = trim($row[1] ?? '');
                $namaMesinMeja = trim($row[2] ?? '');
                $noMold = trim($row[3] ?? '');
                $noPart = trim($row[4] ?? '');
                // $noBarang = $row[5] ?? ''; // Ref
                // $namaPart = $row[6] ?? ''; // Ref
                // $type = $row[7] ?? ''; // Ref
                $lot = trim($row[8] ?? '');
                $qtyTotal = (int) ($row[9] ?? 0);
                $targetPagi = (int) ($row[10] ?? 0);
                $targetSiang = (int) ($row[11] ?? 0);
                $targetMalam = (int) ($row[12] ?? 0);
                $catatan = trim($row[13] ?? '');

                // Validation
                if (!in_array($bagian, ['inject', 'assy'])) {
                    $errorRows[] = "Baris $rowNum: Bagian tidak valid (harus inject/assy).";
                    continue;
                }

                if (!$tanggal || !$noPart || !$lot || $qtyTotal <= 0) {
                    $errorRows[] = "Baris $rowNum: Data wajib (Tanggal, No Part, Lot, Qty) tidak lengkap.";
                    continue;
                }

                // Parse Date
                try {
                    if (is_numeric($tanggal)) {
                        $tanggalParsed = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                    } else {
                        $tanggalParsed = Carbon::parse($tanggal)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $errorRows[] = "Baris $rowNum: Format tanggal salah.";
                    continue;
                }

                // Lookup IDs
                $part = Part::where('no_parts', $noPart)->first();
                if (!$part) {
                    $errorRows[] = "Baris $rowNum: Part dengan No '$noPart' tidak ditemukan.";
                    continue;
                }

                $mesinId = null;
                $mejaId = null;
                $moldshopId = null;

                if ($bagian === 'inject') {
                    $mesin = Mesin::where('nama_mesin', $namaMesinMeja)->first();
                    if (!$mesin) {
                        $errorRows[] = "Baris $rowNum: Mesin '$namaMesinMeja' tidak ditemukan.";
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
                        $errorRows[] = "Baris $rowNum: Meja '$namaMesinMeja' tidak ditemukan.";
                        continue;
                    }
                    $mejaId = $meja->id_meja;
                }

                // Prepare Data for Creation
                $data = [
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
                    'total_shift' => $targetPagi + $targetSiang + $targetMalam, // Auto-sum shift targets
                    'catatan' => $catatan,
                    'status_approval' => 'pending'
                ];

                // Create Planning
                $planning = PlanningProduksi::create($data);

                // Prepare Hourly Data (Mapping Excel Columns O-AL to DB keys)
                // Col Index: 0=A ... 14=O
                $hourlyData = [];

                // Pagi (07-14) -> Excel Cols 14-21 (O-V)
                $hourlyData['pagi_jam_07'] = (int)($row[14] ?? 0);
                $hourlyData['pagi_jam_08'] = (int)($row[15] ?? 0);
                $hourlyData['pagi_jam_09'] = (int)($row[16] ?? 0);
                $hourlyData['pagi_jam_10'] = (int)($row[17] ?? 0);
                $hourlyData['pagi_jam_11'] = (int)($row[18] ?? 0);
                $hourlyData['pagi_jam_12'] = (int)($row[19] ?? 0);
                $hourlyData['pagi_jam_13'] = (int)($row[20] ?? 0);
                $hourlyData['pagi_jam_14'] = (int)($row[21] ?? 0);

                // Siang (15-22) -> Excel Cols 22-29 (W-AD)
                $hourlyData['siang_jam_15'] = (int)($row[22] ?? 0);
                $hourlyData['siang_jam_16'] = (int)($row[23] ?? 0);
                $hourlyData['siang_jam_17'] = (int)($row[24] ?? 0);
                $hourlyData['siang_jam_18'] = (int)($row[25] ?? 0);
                $hourlyData['siang_jam_19'] = (int)($row[26] ?? 0);
                $hourlyData['siang_jam_20'] = (int)($row[27] ?? 0);
                $hourlyData['siang_jam_21'] = (int)($row[28] ?? 0);
                $hourlyData['siang_jam_22'] = (int)($row[29] ?? 0);

                // Malam (23-06) -> Excel Cols 30-37 (AE-AL)
                $hourlyData['malam_jam_23'] = (int)($row[30] ?? 0);
                $hourlyData['malam_jam_00'] = (int)($row[31] ?? 0);
                $hourlyData['malam_jam_01'] = (int)($row[32] ?? 0);
                $hourlyData['malam_jam_02'] = (int)($row[33] ?? 0);
                $hourlyData['malam_jam_03'] = (int)($row[34] ?? 0);
                $hourlyData['malam_jam_04'] = (int)($row[35] ?? 0);
                $hourlyData['malam_jam_05'] = (int)($row[36] ?? 0);
                $hourlyData['malam_jam_06'] = (int)($row[37] ?? 0);

                // Save Hourly Data using existing helper
                $this->saveHourData($planning->id, $hourlyData);

                // Generate Signature
                $this->generateDigitalSignature($planning);

                $successCount++;
            }

            if ($successCount > 0) {
                $msg = "Berhasil import $successCount planning.";
                if (count($errorRows) > 0) {
                    $msg .= " Gagal: " . count($errorRows) . " baris. (Lihat Log/Info)";
                    return back()->with('success', $msg)->with('import_errors', $errorRows);
                }
                return back()->with('success', $msg);
            } else {
                return back()->with('error', 'Gagal import. Cek file anda. Errors: ' . implode(', ', array_slice($errorRows, 0, 5)));
            }
        } catch (\Exception $e) {
            Log::error('Import Failure: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat import.');
        }
    }
}
