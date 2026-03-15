<?php

namespace App\Http\Controllers;

use App\Models\PlanningProduksi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlanningReportController extends Controller
{
    public function cetak(Request $request)
    {
        $filterTanggal = $request->get('tanggal', 'besok');
        $filterBagian = $request->get('bagian', 'all');

        if ($filterTanggal === 'besok') {
            $tanggalFilter = Carbon::tomorrow()->format('Y-m-d');
        } elseif ($filterTanggal === 'hari_ini') {
            $tanggalFilter = now()->format('Y-m-d');
        } else {
            $tanggalFilter = $filterTanggal;
        }

        $planningsInject = collect();
        $planningsAssy = collect();

        if ($filterBagian === 'all' || $filterBagian === 'inject') {
            $planningsInject = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam', 'approver'])
                ->where('bagian', 'inject')
                ->whereDate('tanggal', $tanggalFilter)
                ->orderBy('moldshop_id')->orderBy('tanggal')->get();
        }

        if ($filterBagian === 'all' || $filterBagian === 'assy') {
            $planningsAssy = PlanningProduksi::with(['meja', 'part', 'jam', 'approver'])
                ->where('bagian', 'assy')
                ->whereDate('tanggal', $tanggalFilter)
                ->orderBy('meja_id')->orderBy('tanggal')->get();
        }

        $creatorName = 'Diar W';
        $creatorDate = now()->format('d-m-Y');

        return view('planning.cetak', compact('planningsInject', 'planningsAssy', 'creatorName', 'creatorDate', 'tanggalFilter', 'filterBagian'));
    }

    public function cetakByTanggal(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $plannings = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('moldshop_id')->orderBy('tanggal')->get();

        return view('planning.cetak', [
            'planningsInject' => $plannings->where('bagian', 'inject'),
            'planningsAssy' => $plannings->where('bagian', 'assy'),
            'creatorName' => 'Diar W',
            'creatorDate' => now()->format('d-m-Y'),
            'tanggalFilter' => $tanggal,
            'filterBagian' => 'all'
        ]);
    }

    public function cetakByRange(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-d'));

        $plannings = PlanningProduksi::with(['moldshop', 'mesin', 'part', 'jam'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('moldshop_id')->orderBy('tanggal')->get();

        return view('planning.cetak', [
            'planningsInject' => $plannings->where('bagian', 'inject'),
            'planningsAssy' => $plannings->where('bagian', 'assy'),
            'creatorName' => 'Diar W',
            'creatorDate' => now()->format('d-m-Y'),
            'tanggalFilter' => "$tanggalMulai s/d $tanggalSelesai",
            'filterBagian' => 'all'
        ]);
    }
}
