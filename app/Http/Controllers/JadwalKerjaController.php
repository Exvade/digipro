<?php

namespace App\Http\Controllers;

use App\Models\JadwalKerja;
use App\Http\Requests\JadwalKerjaRequest;
use App\Services\JadwalKerjaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalKerjaController extends Controller
{
    public function __construct(protected JadwalKerjaService $jadwalService) {}

    public function index(Request $request)
    {
        $res = $this->jadwalService->getIndexData($request);
        return isset($res['redirect']) ? redirect($res['redirect']) : view($res['view'], $res['data']);
    }

    public function operatorIndex(Request $request)
    {
        return in_array(Auth::user()->role, ['operator', 'leader', 'foreman']) 
            ? view('jadwal-kerja.operator-index', $this->jadwalService->getOperatorIndexData(Auth::user(), $request)) 
            : redirect()->route('jadwal-kerja.index');
    }

    public function show($id)
    {
        return view('jadwal-kerja.show', ['jadwal' => JadwalKerja::with('user')->findOrFail($id)]);
    }

    public function getUserShift(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'tanggal' => 'nullable|date']);
        return response()->json($this->jadwalService->getUserShiftResponse($request));
    }

    public function store(JadwalKerjaRequest $request)
    {
        try {
            return response()->json(['success' => true, 'message' => 'Simpan berhasil', 'data' => $this->jadwalService->storeJadwal($request->validated())]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }

    public function edit($id)
    {
        try { return response()->json(['success' => true, 'data' => JadwalKerja::with('user')->findOrFail($id)]); } 
        catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'Tidak ditemukan'], 404); }
    }

    public function update(JadwalKerjaRequest $request, $id)
    {
        try {
            return response()->json(['success' => true, 'message' => 'Update berhasil', 'data' => $this->jadwalService->updateJadwal($id, $request->validated())]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }

    public function destroy($id)
    {
        try {
            $this->jadwalService->deleteJadwal($id);
            return response()->json(['success' => true, 'message' => 'Hapus berhasil']);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() == 403 ? 403 : 500); }
    }

    public function downloadTemplate()
    {
        return $this->jadwalService->downloadTemplateResponse();
    }

    public function importExcel(Request $request)
    {
        if (in_array(Auth::user()->role, ['leader', 'foreman', 'operator'])) return back()->with('error', 'Unauthorized.');
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls|max:2048']);
        return $this->jadwalService->handleImportExcel($request->file('excel_file'));
    }
}
