<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use App\Models\Mesin;
use App\Models\OeeTarget;
use App\Models\DowntimeCode;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function targets()
    {
        $machines = Mesin::all();
        $targets = OeeTarget::with('mesin')->paginate(10);
        
        return view('oee.settings.targets', compact('machines', 'targets'));
    }
    
    public function storeTarget(Request $request)
    {
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'target_oee' => 'required|numeric|min:0|max:100',
            'target_availability' => 'required|numeric|min:0|max:100',
            'target_performance' => 'required|numeric|min:0|max:100',
            'target_quality' => 'required|numeric|min:0|max:100',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        OeeTarget::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Target OEE berhasil ditambahkan'
        ]);
    }
    
    public function importTargets()
    {
        return view('oee.settings.import-targets');
    }
    
    public function processImportTargets(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        
        // Implementation for Excel import
        // You can use Maatwebsite\Excel package
        
        return response()->json([
            'success' => true,
            'message' => 'Import target berhasil'
        ]);
    }
    
    public function exportTargets()
    {
        $targets = OeeTarget::with('mesin')->get();
        
        // Implementation for Excel export
        // You can use Maatwebsite\Excel package
        
        return response()->json([
            'success' => true,
            'message' => 'Export target berhasil'
        ]);
    }
    
    public function targetDetails(OeeTarget $target)
    {
        return view('oee.settings.target-details', compact('target'));
    }
    
    public function editTarget(OeeTarget $target)
    {
        $machines = Mesin::all();
        return view('oee.settings.edit-target', compact('target', 'machines'));
    }
    
    public function updateTarget(Request $request, OeeTarget $target)
    {
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'target_oee' => 'required|numeric|min:0|max:100',
            'target_availability' => 'required|numeric|min:0|max:100',
            'target_performance' => 'required|numeric|min:0|max:100',
            'target_quality' => 'required|numeric|min:0|max:100',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        $target->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Target OEE berhasil diperbarui'
        ]);
    }
    
    public function deleteTarget(OeeTarget $target)
    {
        $target->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Target OEE berhasil dihapus'
        ]);
    }
    
    public function downtimeCodes()
    {
        $downtimeCodes = DowntimeCode::with('category')->ordered()->get();
        
        return view('oee.settings.downtime-codes', compact('downtimeCodes'));
    }
    
    public function storeDowntimeCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:downtime_codes,code',
            'name' => 'required|string|max:255',
            'downtime_category_id' => 'required|exists:downtime_categories,id',
            'type' => 'required|in:planned,unplanned',
            'responsibility' => 'required|in:operator,maintenance,supervisor,external',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean'
        ]);
        
        $data = $request->all();
        $data['requires_approval'] = $request->has('requires_approval');
        $data['is_active'] = $request->has('is_active');
        
        DowntimeCode::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Kode downtime berhasil ditambahkan'
        ]);
    }
    
    public function updateDowntimeCode(Request $request, DowntimeCode $downtimeCode)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:downtime_codes,code,' . $downtimeCode->id,
            'name' => 'required|string|max:255',
            'downtime_category_id' => 'required|exists:downtime_categories,id',
            'type' => 'required|in:planned,unplanned',
            'responsibility' => 'required|in:operator,maintenance,supervisor,external',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean'
        ]);
        
        $data = $request->all();
        $data['requires_approval'] = $request->has('requires_approval');
        $data['is_active'] = $request->has('is_active');
        
        $downtimeCode->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Kode downtime berhasil diperbarui'
        ]);
    }
    
    public function deleteDowntimeCode(DowntimeCode $downtimeCode)
    {
        $downtimeCode->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Kode downtime berhasil dihapus'
        ]);
    }
    
    public function exportDowntimeCodes()
    {
        $downtimeCodes = DowntimeCode::with('category')->get();
        
        // Implementation for Excel export
        // You can use Maatwebsite\Excel package
        
        return response()->json([
            'success' => true,
            'message' => 'Export kode downtime berhasil',
            'data' => $downtimeCodes
        ]);
    }
} 