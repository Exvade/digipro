<?php

namespace App\Http\Controllers;

use App\Models\Grup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grups = Grup::with(['leader', 'users'])->get();
        return view('grup.index', compact('grups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leaders = User::where('role', 'leader')->get();
        return view('grup.create', compact('leaders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_grup' => 'required|string|max:255',
            'bagian' => 'required|in:inject,assy',
            'shift' => 'required|in:pagi,siang,malam,all',
            'area' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $grup = Grup::create([
                'nama_grup' => $request->nama_grup,
                'bagian' => $request->bagian,
                'shift' => $request->shift,
                'area' => $request->area,
                'deskripsi' => $request->deskripsi,
                'leader_id' => $request->leader_id,
            ]);

            // Update leader's grup_id if assigned
            if ($request->leader_id) {
                User::where('id', $request->leader_id)->update(['grup_id' => $grup->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grup berhasil dibuat.',
                    'data' => $grup
                ]);
            }

            return redirect()->route('grup.index')
                ->with('success', 'Grup berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Grup $grup)
    {
        $grup->load(['leader', 'users', 'operators']);
        return view('grup.show', compact('grup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grup $grup)
    {
        $leaders = User::where('role', 'leader')->get();
        return view('grup.edit', compact('grup', 'leaders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grup $grup)
    {
        $request->validate([
            'nama_grup' => 'required|string|max:255',
            'bagian' => 'required|in:inject,assy',
            'shift' => 'required|in:pagi,siang,malam,all',
            'area' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Remove old leader assignment
            if ($grup->leader_id) {
                User::where('id', $grup->leader_id)->update(['grup_id' => null]);
            }

            $grup->update([
                'nama_grup' => $request->nama_grup,
                'bagian' => $request->bagian,
                'shift' => $request->shift,
                'area' => $request->area,
                'deskripsi' => $request->deskripsi,
                'leader_id' => $request->leader_id,
            ]);

            // Update new leader's grup_id
            if ($request->leader_id) {
                User::where('id', $request->leader_id)->update(['grup_id' => $grup->id]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grup berhasil diperbarui.',
                    'data' => $grup
                ]);
            }

            return redirect()->route('grup.index')
                ->with('success', 'Grup berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grup $grup)
    {
        try {
            DB::beginTransaction();

            // Remove grup_id from all users in this grup
            User::where('grup_id', $grup->id)->update(['grup_id' => null]);

            $grup->delete();

            DB::commit();

            return redirect()->route('grup.index')
                ->with('success', 'Grup berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get grups by bagian and shift
     */
    public function getByBagianAndShift(Request $request)
    {
        $bagian = $request->bagian;
        $shift = $request->shift;

        $grups = Grup::where('bagian', $bagian)
            ->where(function($query) use ($shift) {
                $query->where('shift', $shift)
                      ->orWhere('shift', 'all');
            })
            ->where('status', 'aktif')
            ->get();

        return response()->json($grups);
    }

    /**
     * Assign users to grup
     */
    public function assignUsers(Request $request, Grup $grup)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Remove users from other grups
            User::whereIn('id', $request->user_ids)->update(['grup_id' => null]);

            // Assign users to this grup
            User::whereIn('id', $request->user_ids)->update(['grup_id' => $grup->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditugaskan ke grup.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 