<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parts = Part::orderBy('created_at', 'desc')->get();
        
        return view('parts.index', compact('parts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('parts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_parts' => 'required|string|max:50|unique:parts,no_parts',
            'nama_part' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'bruto' => 'required|numeric|min:0',
        ], [
            'no_parts.required' => 'Nomor part wajib diisi',
            'no_parts.unique' => 'Nomor part sudah terdaftar',
            'nama_part.required' => 'Nama part wajib diisi',
            'type.required' => 'Type part wajib diisi',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.integer' => 'Quantity harus berupa angka',
            'quantity.min' => 'Quantity minimal 0',
            'bruto.required' => 'Bruto wajib diisi',
            'bruto.numeric' => 'Bruto harus berupa angka',
            'bruto.min' => 'Bruto minimal 0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $part = Part::create([
                'no_parts' => $request->no_parts,
                'nama_part' => $request->nama_part,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'bruto' => $request->bruto,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Part berhasil ditambahkan',
                    'data' => $part
                ]);
            }

            return redirect()->route('parts.index')
                ->with('success', 'Part berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Part $part)
    {
        return response()->json($part);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Part $part)
    {
        return view('parts.update', compact('part'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Part $part)
    {
        $validator = Validator::make($request->all(), [
            'no_parts' => 'required|string|max:50|unique:parts,no_parts,' . $part->id,
            'nama_part' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'bruto' => 'required|numeric|min:0',
        ], [
            'no_parts.required' => 'Nomor part wajib diisi',
            'no_parts.unique' => 'Nomor part sudah terdaftar',
            'nama_part.required' => 'Nama part wajib diisi',
            'type.required' => 'Type part wajib diisi',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.integer' => 'Quantity harus berupa angka',
            'quantity.min' => 'Quantity minimal 0',
            'bruto.required' => 'Bruto wajib diisi',
            'bruto.numeric' => 'Bruto harus berupa angka',
            'bruto.min' => 'Bruto minimal 0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $part->update([
                'no_parts' => $request->no_parts,
                'nama_part' => $request->nama_part,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'bruto' => $request->bruto,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Part berhasil diperbarui',
                    'data' => $part
                ]);
            }

            return redirect()->route('parts.index')
                ->with('success', 'Part berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Part $part)
    {
        try {
            DB::beginTransaction();

            // Check if part is used in checksheets
            if ($part->checksheets()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Part tidak dapat dihapus karena masih digunakan dalam checksheet'
                    ], 422);
                }
                
                return redirect()->back()
                    ->with('error', 'Part tidak dapat dihapus karena masih digunakan dalam checksheet');
            }

            $part->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Part berhasil dihapus'
                ]);
            }

            return redirect()->route('parts.index')
                ->with('success', 'Part berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    /**
     * Get part data for AJAX requests
     */
    public function getPartData(Part $part)
    {
        return response()->json($part);
    }

    /**
     * Search parts
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $parts = Part::where('no_parts', 'like', "%{$search}%")
                    ->orWhere('nama_part', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($parts);
    }
} 