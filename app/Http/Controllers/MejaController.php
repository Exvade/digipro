<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MejaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mejas = Meja::all();
        return view('meja.index', compact('mejas'));
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
            $view = view('meja.create');
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
        $request->validate([
            'nama_meja' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
        ]);

        Meja::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data meja berhasil ditambahkan.'
            ]);
        }

        return redirect()->route('meja.index')
            ->with('success', 'Data meja berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Meja $meja)
    {
        return view('meja.show', compact('meja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Meja $meja)
    {
        return view('meja.edit', compact('meja'));
    }

    /**
     * Show the form for editing the specified resource in modal.
     */
    public function editModal(Meja $meja)
    {
        return view('meja.update', compact('meja'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meja $meja)
    {
        $request->validate([
            'nama_meja' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
        ]);

        $meja->update($request->all());

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data meja berhasil diperbarui.'
            ]);
        }

        return redirect()->route('meja.index')
            ->with('success', 'Data meja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meja $meja)
    {
        $meja->delete();

        return redirect()->route('meja.index')
            ->with('success', 'Data meja berhasil dihapus.');
    }
} 