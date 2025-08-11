<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use App\Models\Mesin;
use App\Models\OeeData;
use App\Models\OeeTarget;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Mesin::with(['oeeData' => function($query) {
            $query->where('tanggal', Carbon::today());
        }])->paginate(12);
        
        // Get statistics from all machines (not paginated)
        $allMachines = Mesin::all();
        $statistics = [
            'total' => $allMachines->count(),
            'aktif' => $allMachines->where('status', 'aktif')->count(),
            'maintenance' => $allMachines->where('status', 'maintenance')->count(),
            'tidak_aktif' => $allMachines->where('status', 'tidak aktif')->count(),
        ];
        
        return view('oee.machines.index', compact('machines', 'statistics'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_mesin' => 'required|string|max:255',
            'brand_mesin' => 'required|string|max:255',
            'tonase_mesin' => 'required|integer|min:1',
            'status' => 'required|in:aktif,maintenance,tidak aktif'
        ]);
        
        $mesin = Mesin::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil ditambahkan',
            'data' => $mesin
        ]);
    }
    
    public function status()
    {
        $machines = Mesin::with(['oeeData' => function($query) {
            $query->where('tanggal', Carbon::today());
        }])->get();
        
        return view('oee.machines.status', compact('machines'));
    }
    
    public function maintenance()
    {
        $machines = Mesin::where('status', 'maintenance')
            ->with(['oeeData' => function($query) {
                $query->where('tanggal', Carbon::today());
            }])
            ->get();
        
        return view('oee.machines.maintenance', compact('machines'));
    }
    
    public function getStatusCounts()
    {
        $machines = Mesin::all();
        
        return response()->json([
            'active' => $machines->where('status', 'aktif')->count(),
            'maintenance' => $machines->where('status', 'maintenance')->count(),
            'inactive' => $machines->where('status', 'tidak aktif')->count(),
            'total' => $machines->count()
        ]);
    }
    
    public function changeStatus(Request $request)
    {
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'new_status' => 'required|in:aktif,maintenance,tidak aktif',
            'reason' => 'required|string'
        ]);
        
        $mesin = Mesin::findOrFail($request->mesin_id);
        $mesin->update(['status' => $request->new_status]);
        
        // Log the status change (you can create a separate table for this)
        
        return response()->json([
            'success' => true,
            'message' => 'Status mesin berhasil diubah'
        ]);
    }
    
    public function export()
    {
        $machines = Mesin::with(['oeeData' => function($query) {
            $query->where('tanggal', Carbon::today());
        }])->get();
        
        // Implementation for Excel export
        // You can use Maatwebsite\Excel package
        
        return response()->json([
            'success' => true,
            'message' => 'Export berhasil'
        ]);
    }
    
    public function show(Mesin $machine)
    {
        $machine->load(['oeeData' => function($query) {
            $query->where('tanggal', '>=', Carbon::now()->subDays(30));
        }, 'oeeTargets', 'downtimeEvents', 'qualityEvents']);
        
        return view('oee.machines.show', compact('machine'));
    }
    
    public function edit(Mesin $machine)
    {
        return view('oee.machines.edit', compact('machine'));
    }
    
    public function update(Request $request, Mesin $machine)
    {
        $request->validate([
            'nama_mesin' => 'required|string|max:255',
            'brand_mesin' => 'required|string|max:255',
            'tonase_mesin' => 'required|integer|min:1',
            'status' => 'required|in:aktif,maintenance,tidak aktif'
        ]);
        
        $machine->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil diperbarui'
        ]);
    }
    
    public function destroy(Mesin $machine)
    {
        $machine->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil dihapus'
        ]);
    }
    
    public function oeeHistory(Mesin $machine)
    {
        $oeeHistory = $machine->oeeData()
            ->where('tanggal', '>=', Carbon::now()->subDays(30))
            ->orderBy('tanggal', 'desc')
            ->get();
            
        $target = $machine->getCurrentTarget();
        
        return view('oee.machines.oee-history', compact('machine', 'oeeHistory', 'target'));
    }
} 