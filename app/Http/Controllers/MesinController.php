<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                'status' => 'required|in:aktif,maintenance,tidak aktif',
            ]);

            $mesin = Mesin::create($request->all());

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
        return view('mesin.edit', compact('mesin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mesin $mesin)
    {
        $request->validate([
            'nama_mesin' => 'required|string|max:255',
            'brand_mesin' => 'required|string|max:255',
            'tonase_mesin' => 'required|numeric|min:0|max:999999',
            'status' => 'required|in:aktif,maintenance,tidak aktif',
        ]);

        $mesin->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil diperbarui',
                'data' => $mesin
            ]);
        }

        return redirect()->route('mesin.index')->with('success', 'Mesin berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mesin $mesin)
    {
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
            ], 500);
        }
    }
} 