<?php

namespace App\Http\Controllers;

use App\Models\Moldshop;
use Illuminate\Http\Request;

class MoldshopController extends Controller
{
    public function index()
    {
        $moldshops = Moldshop::orderBy('no_mold', 'asc')->get();
        return view('moldshop.index', compact('moldshops'));
    }

    public function create()
    {
        return view('moldshop.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_mold' => 'required|unique:moldshops,no_mold',
            'nama_mold' => 'required',
            'type' => 'required',
            'customer' => 'required',
            'status' => 'required|in:OK,REPAIR,PREVENTIVE', // validasi status
        ]);

        Moldshop::create($request->all());

        return redirect()->route('moldshop.index')->with('success', 'Data Mold berhasil ditambahkan!');
    }

    public function edit(Moldshop $moldshop)
    {
        return view('moldshop.edit', compact('moldshop'));
    }

    public function update(Request $request, Moldshop $moldshop)
    {
        $request->validate([
            'no_mold' => 'required|unique:moldshops,no_mold,' . $moldshop->id,
            'nama_mold' => 'required',
            'type' => 'required',
            'customer' => 'required',
            'status' => 'required|in:OK,REPAIR,PREVENTIVE', // validasi status
        ]);

        $moldshop->update($request->all());

        return redirect()->route('moldshop.index')->with('success', 'Data Mold berhasil diperbarui!');
    }

    public function destroy(Moldshop $moldshop)
    {
        $moldshop->delete();
        return redirect()->route('moldshop.index')->with('success', 'Data Mold berhasil dihapus!');
    }

    public function history()
    {
        $moldshops = Moldshop::withSum('inputProduksi as total_ok', 'ok_count')
            ->withSum('inputProduksi as total_ng', 'ng_count')
            ->orderBy('total_ok', 'desc')
            ->get();

        foreach ($moldshops as $mold) {
            $mold->total_shoot = ($mold->total_ok ?? 0) + ($mold->total_ng ?? 0);
        }

        return view('moldshop.history', compact('moldshops'));
    }

    public function usageDetails($id)
    {
        $mold = Moldshop::findOrFail($id);

        $usage = \App\Models\PlanningProduksi::where('moldshop_id', $id)
            ->with(['part', 'inputProduksi'])
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'tanggal' => $p->tanggal->format('d-m-Y'),
                    'part' => $p->part->nama_part ?? '-',
                    'ok' => $p->inputProduksi->ok_count ?? 0,
                    'ng' => $p->inputProduksi->ng_count ?? 0,
                    'total' => ($p->inputProduksi->ok_count ?? 0) + ($p->inputProduksi->ng_count ?? 0)
                ];
            });

        return response()->json([
            'success' => true,
            'mold' => $mold,
            'usage' => $usage
        ]);
    }
}
