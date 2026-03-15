<?php

namespace App\Http\Controllers;

use App\Models\PlanningProduksi;
use App\Services\PlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanningApprovalController extends Controller
{
    protected $planningService;

    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    /**
     * Halaman approval untuk Kabag PPIC
     */
    public function approvalPage(Request $request)
    {
        $user = Auth::user();
        $allowedDepartemen = ['PPIC', 'Production Planning Inventory Control'];

        if ($user->role !== 'super_admin' && ($user->role !== 'kabag' || !in_array($user->departemen, $allowedDepartemen))) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        $filterTanggal = $request->get('tanggal', Carbon::tomorrow()->format('Y-m-d'));

        $plannings = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jadwalKerja', 'jam'])
            ->where('status_approval', 'pending')
            ->when($filterTanggal, fn($q) => $q->whereDate('tanggal', $filterTanggal))
            ->latest('tanggal')
            ->paginate(15);

        return view('planning.approval', compact('plannings', 'filterTanggal'));
    }

    /**
     * Approval planning
     */
    public function approval(Request $request)
    {
        $request->validate([
            'planning_id' => 'required|exists:planning_produksi,id',
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject'
        ]);

        $user = Auth::user();
        $allowedDepartemen = ['PPIC', 'Production Planning Inventory Control'];
        if ($user->role !== 'super_admin' && ($user->role !== 'kabag' || !in_array($user->departemen, $allowedDepartemen))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $planning = PlanningProduksi::findOrFail($request->planning_id);

        if ($planning->status_approval !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Planning sudah diproses.'], 400);
        }

        if ($request->action == 'approve') {
            $qrCodeFilename = $this->planningService->generateApprovalQrCode($planning, $user);
            $planning->update([
                'status_approval' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_qr_code' => $qrCodeFilename
            ]);
            $message = 'Planning disetujui.';
        } else {
            $planning->update([
                'status_approval' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);
            $message = 'Planning ditolak.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
