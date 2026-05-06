<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\Part;
use App\Models\PlanningProduksi;
use App\Http\Requests\Mesin\StoreMesinRequest;
use App\Http\Requests\Mesin\UpdateMesinRequest;
use App\Services\MesinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MesinController extends Controller
{
    public function __construct(protected MesinService $mesinService) {}

    public function index()
    {
        return view('mesin.index', ['mesins' => Mesin::all()]);
    }

    public function create()
    {
        if (!auth()->check()) return response()->json(['error' => 'User not authenticated'], 401);
        return view('mesin.create');
    }

    public function store(StoreMesinRequest $request)
    {
        try {
            $mesin = $this->mesinService->handleStore($request->validated(), auth()->id());
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Berhasil ditambahkan', 'data' => $mesin]);
            }
            return redirect()->route('mesin.index')->with('success', 'Mesin berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error("Error store Mesin", ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Mesin $mesin)
    {
        return view('mesin.show', compact('mesin'));
    }

    public function edit(Mesin $mesin)
    {
        $user = auth()->user();
        $isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
        
        if (in_array($user->role, ['staff', 'operator']) && !$isRMStaff) {
            abort(403, 'Unauthorized action.');
        }

        return view('mesin.edit', [
            'mesin' => $mesin,
            'parts' => Part::orderBy('nama_part', 'asc')->get(),
            'currentPlanning' => PlanningProduksi::where('mesin_id', $mesin->id)->whereDate('tanggal', now()->format('Y-m-d'))->first()
        ]);
    }

    public function update(UpdateMesinRequest $request, Mesin $mesin)
    {
        try {
            $this->mesinService->handleUpdate($mesin, $request->validated(), auth()->id());
            return response()->json(['success' => true, 'message' => 'Data mesin berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error update mesin', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Mesin $mesin)
    {
        if (in_array(auth()->user()->role, ['leader', 'staff'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $this->mesinService->handleDelete($mesin);
            return response()->json(['success' => true, 'message' => 'Mesin berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
