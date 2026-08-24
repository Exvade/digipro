<?php

namespace App\Http\Controllers;

use App\Http\Requests\Planning\StorePlanningRequest;
use App\Http\Requests\Planning\UpdatePlanningRequest;
use App\Models\PlanningProduksi;
use App\Models\Moldshop;
use App\Models\Mesin;
use App\Models\Part;
use App\Models\JadwalKerja;
use App\Models\Meja;
use App\Services\PlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PlanningController extends Controller
{
    protected $planningService;

    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    /**
     * Tampilkan semua planning produksi
     */
    public function index(Request $request)
    {
        $filterTanggal = $request->get('tanggal', date('Y-m-d'));

        $query = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'meja',
            'part',
            'jadwalKerja',
            'jam',
            'approver'
        ])->where('bagian', 'inject');

        if ($filterTanggal) {
            $query->whereDate('tanggal', $filterTanggal);
        }

        $plannings = $query->latest('tanggal')->paginate(10);

        $moldshops = Moldshop::all();
        $mesins    = Mesin::all();
        $mejas     = Meja::all();
        $parts     = Part::all();
        $jadwals   = JadwalKerja::all();

        return view('planning.index', compact('plannings', 'moldshops', 'mesins', 'mejas', 'parts', 'jadwals', 'filterTanggal'));
    }

    /**
     * Tampilkan riwayat planning produksi
     */
    public function history(Request $request)
    {
        $filterTanggal = $request->get('tanggal', Carbon::yesterday()->format('Y-m-d'));

        $plannings = PlanningProduksi::with([
            'moldshop',
            'mesin',
            'part',
            'jadwalKerja',
            'jam'
        ])
            ->where('bagian', 'inject')
            ->whereDate('tanggal', $filterTanggal)
            ->latest('tanggal')
            ->paginate(20);

        return view('planning.history', compact('plannings', 'filterTanggal'));
    }

    /**
     * Tampilkan detail planning
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

        $dataPerJam = $this->planningService->getHourData($planning->id);

        return response()->json(array_merge([
            'id'         => $planning->id,
            'bagian'     => $planning->bagian,
            'no_mold'    => $planning->moldshop->no_mold ?? '-',
            'mesin'      => $planning->mesin->nama_mesin ?? '-',
            'meja'       => $planning->meja->nama_meja ?? '-',
            'tanggal'    => Carbon::parse($planning->tanggal)->format('Y-m-d'),
            'lot'        => $planning->lot_produksi,
            'no_part'    => $planning->part->no_parts ?? '-',
            'nama_part'  => $planning->part->nama_part ?? '-',
            'type'       => $planning->part->type ?? '-',
            'qty'        => $planning->qty_planning_produksi,
            'status_approval' => $planning->status_approval ?? 'pending',
            'catatan'    => $planning->catatan ?? '-',
            'target_shift_pagi' => $planning->target_shift_pagi ?? 0,
            'target_shift_siang' => $planning->target_shift_siang ?? 0,
            'target_shift_malam' => $planning->target_shift_malam ?? 0,
            'total_shift' => $planning->total_shift ?? 0,
        ], $dataPerJam));
    }

    /**
     * Simpan planning baru
     */
    public function store(StorePlanningRequest $request)
    {
        $validated = $request->validated();

        $validated['total_shift'] = ($validated['target_shift_pagi'] ?? 0) +
            ($validated['target_shift_siang'] ?? 0) +
            ($validated['target_shift_malam'] ?? 0);

        if ($validated['bagian'] === 'inject') {
            $validated['meja_id'] = null;
        } else {
            $validated['moldshop_id'] = null;
            $validated['mesin_id'] = null;
        }

        $planning = PlanningProduksi::create($validated);
        $this->planningService->saveHourData($planning->id, $validated);
        $this->planningService->generateDigitalSignature($planning);

        return redirect()->route('planning.index')->with('success', 'Planning produksi berhasil dibuat.');
    }

    /**
     * Update planning
     */
    public function update(UpdatePlanningRequest $request, $id)
    {
        $validated = $request->validated();

        $validated['total_shift'] = ($validated['target_shift_pagi'] ?? 0) +
            ($validated['target_shift_siang'] ?? 0) +
            ($validated['target_shift_malam'] ?? 0);

        if ($validated['bagian'] === 'inject') {
            $validated['meja_id'] = null;
        } else {
            $validated['moldshop_id'] = null;
            $validated['mesin_id'] = null;
        }

        $planning = PlanningProduksi::findOrFail($id);
        $planning->update($validated);
        $this->planningService->saveHourData($planning->id, $validated);
        $this->planningService->generateDigitalSignature($planning);

        return redirect()->route('planning.index')->with('success', 'Planning produksi berhasil diperbarui.');
    }

    /**
     * Hapus planning
     */
    public function destroy($id)
    {
        try {
            $planning = PlanningProduksi::findOrFail($id);
            
            // Check if it's already used in production (Super Admin can bypass)
            if (auth()->user()->role !== 'super_admin' && \App\Models\InputProduksi::where('planning_id', $planning->id)->exists()) {
                return redirect()->route('planning.index')->with('error', 'Gagal menghapus: Planning ini sudah mulai berjalan di tahapan produksi.');
            }

            // Hapus child records yang tidak auto-cascade
            \App\Models\PlanningProduksiJam::where('planning_id', $planning->id)->delete();
            \App\Models\Ploting::where('planning_id', $planning->id)->delete();
            \App\Models\ProduksiPerJam::where('planning_id', $planning->id)->delete();
            
            $planning->delete();

            return redirect()->route('planning.index')->with('success', 'Planning produksi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('planning.index')->with('error', 'Gagal menghapus planning: ' . $e->getMessage());
        }
    }

    /**
     * Secure route untuk mengakses QR code planning
     */
    public function secureQrCode($id, $type = 'signature')
    {
        try {
            $planning = PlanningProduksi::findOrFail($id);
            $qrCodePath = ($type === 'approval') ? $planning->approval_qr_code : $planning->signature_qr_code;

            if (!$qrCodePath) abort(404, 'QR Code tidak ditemukan');

            $fullPath = storage_path('app/public/' . $qrCodePath);
            if (!file_exists($fullPath)) abort(404, 'QR Code tidak ditemukan');

            return response()->file($fullPath, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=3600'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to access QR code: ' . $e->getMessage());
            abort(404);
        }
    }
}
