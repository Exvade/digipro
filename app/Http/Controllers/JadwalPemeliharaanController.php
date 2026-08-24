<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesin;
use App\Models\JadwalPemeliharaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class JadwalPemeliharaanController extends Controller
{
    /**
     * Display the yearly matrix of maintenance schedules
     */
    public function index(Request $request)
    {
        // Default ke tahun sekarang jika tidak dipilih
        $tahun = $request->get('tahun', date('Y'));
        // Ambil semua mesin yang tidak offline (atau ambil semua), lalu diurutkan secara natural (A-Z berdasar nomor yang terkandung)
        $mesins = Mesin::where('status', '!=', 'offline')->get()->sortBy('nama_mesin', SORT_NATURAL | SORT_FLAG_CASE)->values();

        // Ambil jadwal pemeliharaan untuk tahun tsb
        $jadwals = JadwalPemeliharaan::where('tahun', $tahun)->get();

        // Group data menjadi: $jadwalMap[$mesin_id][$bulan][$minggu] = $jadwal
        $jadwalMap = [];
        foreach ($jadwals as $jadwal) {
            $jadwalMap[$jadwal->mesin_id][$jadwal->bulan][$jadwal->minggu] = $jadwal;
        }

        return view('jadwal-pemeliharaan.index', compact('mesins', 'tahun', 'jadwalMap'));
    }

    /**
     * Display the approval page for pending maintenance realizations
     */
    public function approval(Request $request)
    {
        $user = Auth::user();
        // Hanya Leader R&M atau Admin yang boleh akses page ini
        if ($user->role !== 'super_admin' && !($user->role === 'leader' && $user->departemen === 'Repair & Maintenance') && !in_array($user->role, ['kabag', 'manager_plant'])) {
            abort(403, 'Unauthorized access');
        }

        $pendingJadwals = JadwalPemeliharaan::with('mesin', 'approver')
            ->where('status_aktual', 'pending')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return view('jadwal-pemeliharaan.approval', compact('pendingJadwals'));
    }

    /**
     * Dedicated print view for the yearly maintenance schedule
     */
    public function print(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        // Data sama dengan index, tapi dioptimalkan untuk cetak
        $mesins = Mesin::where('status', '!=', 'offline')
            ->get()
            ->sortBy('nama_mesin', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $jadwals = JadwalPemeliharaan::where('tahun', $tahun)->get();

        $jadwalMap = [];
        foreach ($jadwals as $jadwal) {
            $jadwalMap[$jadwal->mesin_id][$jadwal->bulan][$jadwal->minggu] = $jadwal;
        }

        return view('jadwal-pemeliharaan.print', compact('mesins', 'tahun', 'jadwalMap'));
    }

    /**
     * API endpoint or AJAX endpoint to update/save a cell in the schedule
     */
    public function updateCell(Request $request)
    {
        // Operator hanya boleh update jika memiliki izin spesifik (misal dari R&M)
        // Namun berdasarkan request user terbaru, operator produksi dilarang.
        // Izin di web.php sudah membatasi akses ke route ini.
        
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'minggu' => 'required|integer|between:1,5',
            'plan_tipe' => 'nullable|string|max:50',
            'aktual_tanggal' => 'nullable|string',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
            'keterangan' => 'nullable|string',
        ]);

        $data = [
            'plan_tipe' => $request->plan_tipe,
            'aktual_tanggal' => $request->aktual_tanggal,
            'keterangan' => $request->keterangan,
        ];

        // Jika ada update pada aktual_tanggal, status jadi pending jika sebelumnya kosong atau berubah
        // Namun kita biarkan user manual upload bukti.
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/maintenance_bukti', $filename);
            $data['bukti'] = $filename;
            $data['status_aktual'] = 'pending'; // Set to pending for approval
        }

        $jadwal = JadwalPemeliharaan::updateOrCreate(
            [
                'mesin_id' => $request->mesin_id,
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'minggu' => $request->minggu,
            ],
            $data
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $jadwal
        ]);
    }

    /**
     * Approve maintenance realization
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        // Cek izin (Leader R&M, Kabag, Manager Plant, atau Super Admin)
        $isRMLeader = $user->role === 'leader' && $user->departemen === 'Repair & Maintenance';
        $isAdminRoles = in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff']); // Staff maintenance often handles this too
        
        if (!$isRMLeader && !$isAdminRoles) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki wewenang untuk menyetujui.'], 403);
        }

        $jadwal = JadwalPemeliharaan::findOrFail($id);
        $jadwal->update([
            'status_aktual' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Maintenance disetujui.']);
    }

    /**
     * Reject maintenance realization
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $isRMLeader = $user->role === 'leader' && $user->departemen === 'Repair & Maintenance';
        $isAdminRoles = in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'staff']);
        
        if (!$isRMLeader && !$isAdminRoles) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki wewenang untuk menolak.'], 403);
        }

        $jadwal = JadwalPemeliharaan::findOrFail($id);
        $jadwal->update([
            'status_aktual' => 'rejected',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Maintenance ditolak.']);
    }

    /**
     * Export the yearly schedule to PDF
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        
        $mesins = Mesin::where('status', '!=', 'offline')
            ->get()
            ->sortBy('nama_mesin', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $jadwals = JadwalPemeliharaan::where('tahun', $tahun)->get();

        $jadwalMap = [];
        foreach ($jadwals as $jadwal) {
            $jadwalMap[$jadwal->mesin_id][$jadwal->bulan][$jadwal->minggu] = $jadwal;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('jadwal-pemeliharaan.print', compact('mesins', 'tahun', 'jadwalMap'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Jadwal_Pemeliharaan_{$tahun}.pdf");
    }
}
