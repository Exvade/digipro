<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Mesin;
use App\Models\Part;
use App\Models\User;
use App\Models\Meja;
use App\Models\Grup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedGrup     = $request->get('grup_id');
        $selectedBagian   = $request->get('bagian');
        $selectedTanggal  = $request->get('tanggal'); // ✅ Filter tanggal

        // Base query dengan relasi
        $query = Checksheet::with(['mesin', 'part', 'user.grup', 'meja', 'approver', 'grup'])
            ->orderBy('created_at', 'desc');

        // ✅ Filter grup
        if (!empty($selectedGrup)) {
            $query->whereHas('user', function ($q) use ($selectedGrup) {
                $q->where('grup_id', $selectedGrup);
            });
        }

        // ✅ Filter bagian
        if (!empty($selectedBagian)) {
            $query->whereHas('user', function ($q) use ($selectedBagian) {
                $q->where('bagian', $selectedBagian);
            });
        }

        // ✅ Filter tanggal tunggal (format YYYY-MM-DD)
        if (!empty($selectedTanggal)) {
            try {
                $tanggal = \Carbon\Carbon::parse($selectedTanggal)->format('Y-m-d');
                $query->whereDate('created_at', $tanggal);
            } catch (\Exception $e) {
                // kalau parsing gagal, abaikan filter tanggal
            }
        }

        // Eksekusi query
        $allChecksheets = $query->get();

        // Separate checksheets by section
        $assyChecksheets   = collect();
        $injectChecksheets = collect();

        foreach ($allChecksheets as $checksheet) {
            if ($checksheet->meja_id && !$checksheet->mesin_id) {
                // Kalau ada meja_id tapi tidak ada mesin_id → assy
                $assyChecksheets->push($checksheet);
            } elseif ($checksheet->mesin_id && !$checksheet->meja_id) {
                // Kalau ada mesin_id tapi tidak ada meja_id → inject
                $injectChecksheets->push($checksheet);
            } else {
                // Fallback: cek bagian user
                if ($checksheet->user && $checksheet->user->bagian === 'assy') {
                    $assyChecksheets->push($checksheet);
                } else {
                    $injectChecksheets->push($checksheet);
                }
            }
        }

        // Ambil grup untuk dropdown filter
        $grups = Grup::orderBy('nama_grup')->get();

        return view('checksheet.index', compact(
            'assyChecksheets',
            'injectChecksheets',
            'grups',
            'selectedGrup',
            'selectedBagian',
            'selectedTanggal'
        ));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mesins = Mesin::all();
        $parts = Part::all();
        $users = User::where('role', '!=', 'super_admin')->get();
        $mejas = Meja::where('status', 'aktif')->get();
        $grups = Grup::orderBy('nama_grup')->get();

        return view('checksheet.create', compact('mesins', 'parts', 'users', 'mejas', 'grups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get user details to determine validation rules
        $user = User::find($request->user_id);

        $validationRules = [
            'user_id' => 'required|exists:users,id',
            // Shift validation removed - will be retrieved from jadwal kerja automatically
            'grup_id' => 'nullable|exists:grups,id',
            'foto_apd' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_instruksi_kerja' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_qpoint' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_limit_sampel' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_amptm' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Add conditional validation based on user role and section
        if ($user) {
            if ($user->role === 'leader') {
                if ($user->bagian === 'inject') {
                    $validationRules['mesin_id'] = 'required|exists:mesin,id';
                    $validationRules['part_id'] = 'required|exists:parts,id';
                } elseif ($user->bagian === 'assy') {
                    $validationRules['meja_id'] = 'required|exists:meja,id_meja';
                    $validationRules['part_id'] = 'required|exists:parts,id';
                }
            } else if (in_array($user->role, ['kabag', 'manager_plant', 'super_admin'])) {
                // For kabag, manager_plant, and super_admin, both mesin and meja are optional
                $validationRules['mesin_id'] = 'nullable|exists:mesin,id';
                $validationRules['part_id'] = 'nullable|exists:parts,id';
                $validationRules['meja_id'] = 'nullable|exists:meja,id_meja';
            } else {
                // For other roles, require mesin_id and part_id
                $validationRules['mesin_id'] = 'required|exists:mesin,id';
                $validationRules['part_id'] = 'required|exists:parts,id';
            }
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            // Note: shift is no longer stored, it will be retrieved from jadwal kerja via accessor
            $checksheetData = [
                'user_id' => $request->user_id,
                'grup_id' => $request->grup_id,
                'amptm_standar' => $request->amptm_standar,
                'approval_status' => 'pending',
            ];

            // Set mesin_id and part_id based on user role and section
            if ($user && $user->role === 'leader' && $user->bagian === 'assy') {
                $checksheetData['meja_id'] = $request->meja_id;
                $checksheetData['part_id'] = $request->part_id;
                $checksheetData['mesin_id'] = null;
            } else {
                $checksheetData['mesin_id'] = $request->mesin_id;
                $checksheetData['part_id'] = $request->part_id;
                $checksheetData['meja_id'] = $request->meja_id;
            }

            $checksheet = Checksheet::create($checksheetData);

            // Handle photo uploads
            $photoTypes = ['foto_apd', 'foto_instruksi_kerja', 'foto_qpoint', 'foto_limit_sampel', 'foto_amptm'];

            foreach ($photoTypes as $type) {
                if ($request->hasFile($type)) {
                    $file = $request->file($type);
                    $path = $file->store('checksheet-photos', 'public');

                    // Map the type correctly
                    $photoType = str_replace('foto_', '', $type);
                    if ($photoType === 'qpoint') {
                        $photoType = 'quality';
                    }

                    $checksheet->photos()->create([
                        'file_path' => $path,
                        'type' => $photoType,
                        'description' => ucfirst(str_replace('foto_', '', $type)) . ' Photo'
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Checksheet berhasil dibuat',
                    'data' => $checksheet
                ]);
            }

            return redirect()->route('checksheet.index')
                ->with('success', 'Checksheet berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat checksheet')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Checksheet $checksheet)
    {
        $checksheet->load(['mesin', 'part', 'user', 'meja', 'approver', 'photos', 'grup']);
        return view('checksheet.show', compact('checksheet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Checksheet $checksheet)
    {
        $mesins = Mesin::where('status', 'aktif')->get();
        $parts = Part::all();
        $users = User::where('role', '!=', 'super_admin')->get();
        $mejas = Meja::where('status', 'aktif')->get();
        $grups = Grup::orderBy('nama_grup')->get();

        // Return ke update.blade.php saja
        return view('checksheet.update', compact('checksheet', 'mesins', 'parts', 'users', 'mejas', 'grups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Checksheet $checksheet)
    {
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'part_id' => 'required|exists:parts,id',
            'user_id' => 'required|exists:users,id',
            'grup_id' => 'nullable|exists:grups,id',
            'meja_id' => 'nullable|exists:meja,id_meja',
            // Shift validation removed - will be retrieved from jadwal kerja automatically
            'foto_apd' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_instruksi_kerja' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_qpoint' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_limit_sampel' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_amptm' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Note: shift is no longer stored, it will be retrieved from jadwal kerja via accessor
            $checksheet->update([
                'mesin_id' => $request->mesin_id,
                'part_id' => $request->part_id,
                'user_id' => $request->user_id,
                'grup_id' => $request->grup_id,
                'meja_id' => $request->meja_id,
                'amptm_standar' => $request->amptm_standar,
            ]);

            // Handle photo uploads
            $photoTypes = ['foto_apd', 'foto_instruksi_kerja', 'foto_qpoint', 'foto_limit_sampel', 'foto_amptm'];

            foreach ($photoTypes as $type) {
                if ($request->hasFile($type)) {
                    // Map the type correctly for deletion
                    $photoType = str_replace('foto_', '', $type);
                    if ($photoType === 'qpoint') {
                        $photoType = 'quality';
                    }

                    // Delete old photo if exists
                    $oldPhoto = $checksheet->photos()->where('type', $photoType)->first();
                    if ($oldPhoto) {
                        Storage::disk('public')->delete($oldPhoto->file_path);
                        $oldPhoto->delete();
                    }

                    // Upload new photo
                    $file = $request->file($type);
                    $path = $file->store('checksheet-photos', 'public');

                    $checksheet->photos()->create([
                        'file_path' => $path,
                        'type' => $photoType,
                        'description' => ucfirst(str_replace('foto_', '', $type)) . ' Photo'
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Checksheet berhasil diperbarui',
                    'data' => $checksheet
                ]);
            }

            return redirect()->route('checksheet.index')
                ->with('success', 'Checksheet berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui checksheet')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Checksheet $checksheet)
    {
        try {
            DB::beginTransaction();

            // Delete associated photos
            foreach ($checksheet->photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
            }
            $checksheet->photos()->delete();

            // Delete checksheet
            $checksheet->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Checksheet berhasil dihapus'
                ]);
            }

            return redirect()->route('checksheet.index')
                ->with('success', 'Checksheet berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus checksheet');
        }
    }
}
