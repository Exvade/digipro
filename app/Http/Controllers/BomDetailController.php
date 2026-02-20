<?php

namespace App\Http\Controllers;

use App\Models\BomDetail;
use App\Models\Moldshop;
use App\Models\Part;
use Illuminate\Http\Request;

class BomDetailController extends Controller
{
    public function index()
    {
        $bomDetails = BomDetail::with(['moldshop', 'part'])->paginate(10);
        $moldshops = Moldshop::all();
        $parts = Part::all();

        return view('bom-details.index', compact('bomDetails', 'moldshops', 'parts'));
    }


    public function create()
    {
        $moldshops = Moldshop::all();
        $parts = Part::all();
        return view('bom-details.create', compact('moldshops', 'parts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'moldshop_id' => 'required|exists:moldshops,id',
            'part_id' => 'required|exists:parts,id',
            'cavity_mold' => 'required|integer|min:1',
            'cycle_time_std' => 'required|numeric|min:0',
            'resin_type' => 'nullable|string|max:50',
            'shot_weight' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        BomDetail::create($validated);

        return redirect()->route('bom-details.index')->with('success', 'Data BOM berhasil ditambahkan.');
    }

    public function edit(BomDetail $bomDetail)
    {
        $moldshops = Moldshop::all();
        $parts = Part::all();
    
        // pastikan view yang kamu pakai sesuai folder dan nama file
        return view('bom-details.update', compact('bomDetail', 'moldshops', 'parts'));
    }
    
    public function update(Request $request, BomDetail $bomDetail)
    {
        $validated = $request->validate([
            'moldshop_id' => 'required|exists:moldshops,id',
            'part_id' => 'required|exists:parts,id',
            'cavity_mold' => 'required|integer|min:1',
            'cycle_time_std' => 'required|numeric|min:0',
            'resin_type' => 'nullable|string|max:50',
            'shot_weight' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $bomDetail->update($validated);

        return redirect()->route('bom-details.index')->with('success', 'Data BOM berhasil diperbarui.');
    }

    public function destroy(BomDetail $bomDetail)
    {
        $bomDetail->delete();
        return redirect()->route('bom-details.index')->with('success', 'Data BOM berhasil dihapus.');
    }
}
