<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OeeController extends Controller
{
    public function index()
    {
        $dataOee = [
            (object)[ 'id' => 1, 'nama_mesin' => 'Mesin A', 'availability' => 95, 'performance' => 90, 'quality' => 98 ],
            (object)[ 'id' => 2, 'nama_mesin' => 'Mesin B', 'availability' => 92, 'performance' => 88, 'quality' => 97 ],
        ];
        return view('oee.dashboard.index', compact('dataOee'));
    }

    public function store(Request $request)
    {
        // Validasi dan simpan data ke DB (dummy)
        return response()->json(['success' => true, 'message' => 'Data OEE berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        // Validasi dan update data ke DB (dummy)
        return response()->json(['success' => true, 'message' => 'Data OEE berhasil diupdate']);
    }
} 