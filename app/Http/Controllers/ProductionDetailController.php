<?php

namespace App\Http\Controllers;

use App\Models\ProductionDetail;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class ProductionDetailController extends Controller
{
    public function create($logId)
    {
        $log = ProductionLog::findOrFail($logId);
        return view('production_details.create', compact('log'));
    }

    public function store(Request $request)
    {
        ProductionDetail::create($request->all());
        return back()->with('success', 'Detail produksi berhasil ditambahkan');
    }
}
