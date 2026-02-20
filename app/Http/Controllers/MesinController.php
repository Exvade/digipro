<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\DowntimeEvent;
use App\Models\PlanningProduksi;
use App\Models\BomDetail;
use Carbon\Carbon;
use App\Services\FontteService;

class MesinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mesins = Mesin::all();
        return view('mesin.index', compact('mesins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::info('create method called');

        // Check if user is authenticated
        if (!auth()->check()) {
            Log::error('User not authenticated');
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            $view = view('mesin.create');
            Log::info('View rendered successfully');
            return $view;
        } catch (\Exception $e) {
            Log::error('Error in create: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['leader', 'staff'])) {
            abort(403, 'Unauthorized action.');
        }

        $requestId = uniqid();
        try {
            Log::info("Store method called - Request ID: {$requestId}", [
                'data' => $request->all(),
                'ajax' => $request->ajax(),
                'method' => $request->method(),
                'url' => $request->url(),
                'user_id' => auth()->id()
            ]);

            $request->validate([
                'nama_mesin' => 'required|string|max:255',
                'brand_mesin' => 'required|string|max:255',
                'tonase_mesin' => 'required|numeric|min:0|max:999999',
                'status' => 'required|in:running,stop,maintenance,offline',
                'stop_reason' => 'nullable|required_if:status,stop,maintenance,offline',
                'keterangan' => 'nullable|string',
            ]);

            $data = $request->all();
            $data['status_since'] = now();
            // Allow stop_reason for stop, maintenance, and offline statuses
            $data['stop_reason'] = in_array($request->status, ['stop', 'maintenance', 'offline']) ? $request->stop_reason : null;
            $mesin = Mesin::create($data);

            // Determine current shift
            $currentHour = (int) date('H');
            $currentShift = ($currentHour >= 7 && $currentHour < 15) ? 'pagi' : (($currentHour >= 15 && $currentHour < 23) ? 'siang' : 'malam');

            // Create initial activity event for the machine
            $initialEventType = $mesin->status;
            if ($mesin->status === 'stop') {
                $initialEventType = $mesin->stop_reason ?: 'stop';
            }

            DowntimeEvent::create([
                'mesin_id' => $mesin->id,
                'user_id' => auth()->id(),
                'tanggal' => now()->format('Y-m-d'),
                'start_time' => now()->format('H:i:s'),
                'downtime_type' => $initialEventType,
                'shift' => $currentShift,
                'status' => 'active',
                'keterangan' => 'Status awal saat registrasi mesin'
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mesin berhasil ditambahkan',
                    'data' => $mesin
                ]);
            }

            return redirect()->route('mesin.index')->with('success', 'Mesin berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error("Error in store method - Request ID: {$requestId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mesin $mesin)
    {
        return view('mesin.show', compact('mesin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mesin $mesin)
    {
        if (in_array(auth()->user()->role, ['staff', 'operator'])) {
            abort(403, 'Unauthorized action.');
        }

        $parts = \App\Models\Part::orderBy('nama_part', 'asc')->get();
        $currentPlanning = \App\Models\PlanningProduksi::where('mesin_id', $mesin->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        return view('mesin.edit', compact('mesin', 'parts', 'currentPlanning'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mesin $mesin)
    {
        Log::critical('DEBUG: MesinController@update hit!', $request->all());

        if (auth()->user()->role === 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengedit data mesin.'
            ], 403);
        }

        if (auth()->user()->role === 'leader') {
            $request->merge([
                'nama_mesin' => $mesin->nama_mesin,
                'brand_mesin' => $mesin->brand_mesin,
                'tonase_mesin' => $mesin->tonase_mesin,
            ]);
        }

        $request->validate([
            'nama_mesin' => 'required|string|max:255',
            'brand_mesin' => 'required|string|max:255',
            'tonase_mesin' => 'required|numeric|min:0|max:999999',
            'status' => 'required|in:running,stop,maintenance,offline',
            'stop_reason' => 'nullable|required_if:status,stop,maintenance,offline',
            'keterangan' => 'nullable|string',
            'part_id' => 'nullable|exists:parts,id',
            'target_produksi' => 'nullable|numeric|min:1',
            'lot_produksi' => 'nullable|string',
        ]);

        $oldStatus = $mesin->status;
        $oldReason = $mesin->stop_reason;
        $oldKeterangan = $mesin->keterangan;
        $newStatus = $request->status;
        $newReason = $request->stop_reason;

        // Determine if state changed
        $stateChanged = ($oldStatus !== $newStatus) || (in_array($newStatus, ['stop', 'maintenance', 'offline']) && $oldReason !== $newReason);

        if ($stateChanged) {
            $mesin->status_since = now();
        }

        $mesin->nama_mesin = $request->nama_mesin;
        $mesin->brand_mesin = $request->brand_mesin;
        $mesin->tonase_mesin = $request->tonase_mesin;
        $mesin->status = $newStatus;
        $mesin->stop_reason = in_array($newStatus, ['stop', 'maintenance', 'offline']) ? $newReason : null;
        $mesin->keterangan = $request->keterangan;
        $mesin->save();

        // Handle Part and Target for Running Status
        if ($newStatus === 'running' && $request->filled('part_id')) {
            $planning = PlanningProduksi::where('mesin_id', $mesin->id)
                ->whereDate('tanggal', now()->format('Y-m-d'))
                ->first();

            if ($planning) {
                $planning->update([
                    'part_id' => $request->part_id,
                    'qty_planning_produksi' => $request->target_produksi,
                    'lot_produksi' => $request->lot_produksi ?? $planning->lot_produksi ?? '-'
                ]);
            } else {
                // Find Mold from BomDetail if possible
                $bomDetail = BomDetail::where('part_id', $request->part_id)->first();

                // Gunakan DB::table untuk menghindari model logic yang mungkin menyisipkan kolom 'status'
                \Illuminate\Support\Facades\DB::table('planning_produksi')->insert([
                    'mesin_id' => $mesin->id,
                    'part_id' => $request->part_id,
                    'qty_planning_produksi' => $request->target_produksi,
                    'lot_produksi' => $request->lot_produksi ?? '-',
                    'tanggal' => now()->format('Y-m-d'),
                    'bagian' => 'inject',
                    'status_approval' => 'approved',
                    'moldshop_id' => $bomDetail ? $bomDetail->moldshop_id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Manage Downtime Events - Close old and start new if state changed or leaks detected
        $hasOpenEvents = DowntimeEvent::where('mesin_id', $mesin->id)->whereNull('end_time')->exists();

        if ($stateChanged || $hasOpenEvents) {
            $now = Carbon::now();

            // Determine current shift for events
            $currentHour = (int) date('H');
            $currentShift = ($currentHour >= 7 && $currentHour < 15) ? 'pagi' : (($currentHour >= 15 && $currentHour < 23) ? 'siang' : 'malam');

            // Helper for description: Prepend reason to keterangan for maintenance to ensure it shows in history
            $formattedOldKeterangan = $oldKeterangan;
            if ($oldStatus === 'maintenance' && $oldReason) {
                $formattedOldKeterangan = $oldReason . ($oldKeterangan ? " - $oldKeterangan" : "");
            }

            // 1. Close ALL previous open events for this machine to ensure no "leaks"
            $openEvents = DowntimeEvent::where('mesin_id', $mesin->id)
                ->whereNull('end_time')
                ->get();

            // CATCH UP: If status changed but NO open event was found in DB,
            // we create a historical record for the PREVIOUS status so history is not lost.
            if ($stateChanged && $openEvents->isEmpty() && $oldStatus) {
                $catchUpStart = $mesin->status_since ? Carbon::parse($mesin->status_since) : $now->copy()->subMinutes(1);

                // Only create if there's a valid previous period
                if ($catchUpStart->lt($now)) {
                    $prevEventType = $oldStatus;
                    if ($oldStatus === 'stop') {
                        $prevEventType = $oldReason ?: 'stop';
                    }

                    DowntimeEvent::create([
                        'mesin_id' => $mesin->id,
                        'user_id' => auth()->id(),
                        'tanggal' => $catchUpStart->format('Y-m-d'),
                        'start_time' => $catchUpStart->format('H:i:s'),
                        'end_time' => $now->format('H:i:s'),
                        'downtime_type' => $prevEventType,
                        'shift' => $currentShift, // Simplified: use current shift
                        'duration_minutes' => max(0, $now->diffInMinutes($catchUpStart)),
                        'status' => 'active',
                        'keterangan' => $formattedOldKeterangan ?: 'Log otomatis (perubahan status)'
                    ]);
                }
            }

            foreach ($openEvents as $event) {
                $event->end_time = $now->format('H:i:s');
                // Use old keterangan for the event being closed if it matches the machine's previous state
                if ($oldStatus === $event->downtime_type || ($oldStatus === 'stop' && !in_array($event->downtime_type, ['running', 'aktif', 'maintenance', 'offline']))) {
                    $event->keterangan = $formattedOldKeterangan;
                }

                $eventStart = Carbon::parse($event->tanggal->format('Y-m-d') . ' ' . $event->start_time);
                $event->duration_minutes = max(0, $now->diffInMinutes($eventStart));
                $event->status = 'active'; // Mark as handled
                $event->save();
            }

            // 2. Create a fresh event for the current state (Running/Stop/etc)
            $eventType = $newStatus;
            if ($newStatus === 'stop') {
                $eventType = $newReason ?: 'stop';
            }

            // Format new description to include reason if maintenance
            $formattedNewKeterangan = $request->keterangan;
            if ($newStatus === 'maintenance' && $newReason) {
                $formattedNewKeterangan = $newReason . ($request->keterangan ? " - " . $request->keterangan : "");
            }

            DowntimeEvent::create([
                'mesin_id' => $mesin->id,
                'user_id' => auth()->id(),
                'tanggal' => $now->format('Y-m-d'),
                'start_time' => $now->format('H:i:s'),
                'downtime_type' => $eventType,
                'shift' => $currentShift,
                'status' => 'active',
                'keterangan' => $formattedNewKeterangan
            ]);

            // 3. Send WhatsApp Notification via Fonnte if state actually changed
            if ($stateChanged) {
                try {
                    $fontteService = new FontteService();
                    $message = $fontteService->formatFullMachineReport(
                        $mesin,
                        $oldStatus,
                        $newStatus,
                        $oldReason,
                        $newReason,
                        $request->keterangan
                    );
                    $fontteService->sendGroupNotification($message);
                } catch (\Exception $e) {
                    Log::error('Failed to send WhatsApp notification', ['error' => $e->getMessage()]);
                }
            }
        } else {
            // No state change and no leaks, just update keterangan on the current open event
            $formattedUpdateKeterangan = $request->keterangan;
            if ($newStatus === 'maintenance' && $newReason) {
                $formattedUpdateKeterangan = $newReason . ($request->keterangan ? " - " . $request->keterangan : "");
            }

            DowntimeEvent::where('mesin_id', $mesin->id)
                ->whereNull('end_time')
                ->update(['keterangan' => $formattedUpdateKeterangan]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data mesin berhasil diperbarui.'
        ]);
    }

    public function destroy(Mesin $mesin)
    {
        if (in_array(auth()->user()->role, ['leader', 'staff'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $mesin->delete();
            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 10);
        }
    }
}
