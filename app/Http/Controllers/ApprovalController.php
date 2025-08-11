<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Checksheet::with(['mesin', 'part', 'user', 'meja', 'approver', 'grup'])
            ->where('approval_status', 'pending');
        
        // Apply filter based on request parameter
        $filter = $request->get('filter', 'all');
        
        if ($filter === 'inject') {
            $query->whereNotNull('mesin_id');
        } elseif ($filter === 'assy') {
            $query->whereNotNull('meja_id');
        }
        
        // Apply grup filter
        $selectedGrup = $request->get('grup_filter');
        if ($selectedGrup) {
            $query->where('grup_id', $selectedGrup);
        }
        
        $checksheets = $query->orderBy('created_at', 'desc')->get();
        
        // Get all grups for filter dropdown
        $grups = \App\Models\Grup::orderBy('nama_grup')->get();
        
        return view('approval.index', compact('checksheets', 'filter', 'grups', 'selectedGrup'));
    }

    /**
     * Approve a checksheet
     */
    public function approve(Request $request, Checksheet $checksheet)
    {
        Log::info('Approval attempt', [
            'checksheet_id' => $checksheet->id,
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'approval_note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null
            ];

            Log::info('Updating checksheet with data', $updateData);

            $checksheet->update($updateData);

            DB::commit();

            Log::info('Checksheet approved successfully', [
                'checksheet_id' => $checksheet->id,
                'updated_data' => $checksheet->fresh()->toArray()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Checksheet berhasil disetujui',
                    'data' => $checksheet
                ]);
            }

            // Only show kabag approval notification for kabag role
            if (auth()->user()->role === 'kabag') {
                return redirect()->route('approval.index')
                    ->with('kabag_approved', 'Checksheet berhasil di-approve oleh Kabag!');
            }

            return redirect()->route('approval.index')
                ->with('success', 'Checksheet berhasil disetujui');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error approving checksheet', [
                'checksheet_id' => $checksheet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyetujui checksheet');
        }
    }

    /**
     * Reject a checksheet
     */
    public function reject(Request $request, Checksheet $checksheet)
    {
        Log::info('Rejection attempt', [
            'checksheet_id' => $checksheet->id,
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ];

            Log::info('Updating checksheet with rejection data', $updateData);

            $checksheet->update($updateData);

            DB::commit();

            Log::info('Checksheet rejected successfully', [
                'checksheet_id' => $checksheet->id,
                'updated_data' => $checksheet->fresh()->toArray()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Checksheet berhasil ditolak',
                    'data' => $checksheet
                ]);
            }

            return redirect()->route('approval.index')
                ->with('success', 'Checksheet berhasil ditolak');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error rejecting checksheet', [
                'checksheet_id' => $checksheet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menolak checksheet');
        }
    }

    /**
     * Approve all pending checksheets
     */
    public function approveAll(Request $request)
    {
        try {
            DB::beginTransaction();

            $pendingChecksheets = Checksheet::where('approval_status', 'pending')->get();
            
            if ($pendingChecksheets->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada checksheet yang menunggu approval'
                ], 400);
            }

            $approvedCount = 0;
            foreach ($pendingChecksheets as $checksheet) {
                $checksheet->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'rejection_reason' => null
                ]);
                $approvedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil approve {$approvedCount} checksheet",
                'approved_count' => $approvedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show approval history
     */
    public function history(Request $request)
    {
        $query = Checksheet::with(['mesin', 'part', 'user', 'meja', 'approver', 'grup'])
            ->whereIn('approval_status', ['approved', 'rejected']);
        
        // Apply grup filter
        $selectedGrup = $request->get('grup_filter');
        if ($selectedGrup) {
            $query->where('grup_id', $selectedGrup);
        }
        
        $checksheets = $query->orderBy('approved_at', 'desc')->get();
        
        // Get all grups for filter dropdown
        $grups = \App\Models\Grup::orderBy('nama_grup')->get();
        
        return view('approval.history', compact('checksheets', 'grups', 'selectedGrup'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Checksheet $checksheet)
    {
        $checksheet->load(['mesin', 'part', 'user', 'meja', 'approver', 'photos', 'grup']);
        return view('approval.show', compact('checksheet'));
    }
} 