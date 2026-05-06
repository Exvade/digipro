<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesin;
use App\Models\JadwalPemeliharaan;
use Carbon\Carbon;

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
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'minggu' => 'required|integer|between:1,5',
            'plan_tipe' => 'nullable|string|max:50',
            'aktual_tanggal' => 'nullable|string',
        ]);

        $jadwal = JadwalPemeliharaan::updateOrCreate(
            [
                'mesin_id' => $request->mesin_id,
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'minggu' => $request->minggu,
            ],
            [
                'plan_tipe' => $request->plan_tipe,
                'aktual_tanggal' => $request->aktual_tanggal,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $jadwal
        ]);
    }
}
