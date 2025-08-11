<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Mesin;
use App\Models\Part;
use App\Models\User;
use App\Models\Meja;
use App\Models\Grup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Exports\ChecksheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Build base query for all statistics
        $baseQuery = Checksheet::query();
        
        // Apply group filter from page filter form
        if (request('filter_grup_id')) {
            Log::info('Applying group filter', [
                'filter_grup_id' => request('filter_grup_id'),
                'filter_grup_id_type' => gettype(request('filter_grup_id'))
            ]);
            
            $baseQuery->whereHas('user', function($q) {
                $q->where('grup_id', request('filter_grup_id'));
            });
        }
        
        // Get summary statistics with filters
        $totalChecksheets = (clone $baseQuery)->count();
        $pendingChecksheets = (clone $baseQuery)->where('approval_status', 'pending')->count();
        $approvedChecksheets = (clone $baseQuery)->where('approval_status', 'approved')->count();
        $rejectedChecksheets = (clone $baseQuery)->where('approval_status', 'rejected')->count();
        
        // Get recent checksheets based on user department
        $recentChecksheetsQuery = (clone $baseQuery)->with(['mesin', 'part', 'user.grup', 'meja', 'approver']);
        
        // Log all request parameters
        Log::info('All request parameters', request()->all());
        
        // Determine report type based on user role and department
        $reportType = 'general'; // default
        $departmentSpecificData = null;
        $userDepartment = null;
        
        if ($user->role === 'leader') {
            // For leaders, determine their department from their profile
            if (isset($user->bagian) && strtolower($user->bagian) === 'assy') {
                $reportType = 'assy';
                $userDepartment = 'assy';
            } elseif (isset($user->bagian) && strtolower($user->bagian) === 'inject') {
                $reportType = 'inject';
                $userDepartment = 'inject';
            }
            // Leaders cannot change their report type via URL - it's fixed to their department
        } elseif (in_array($user->role, ['super_admin', 'kabag', 'manager_plant', 'foreman'])) {
            // For super_admin, kabag, manager_plant, and foreman, check if report_type is specified in URL
            $requestedReportType = request()->get('report_type');
            if ($requestedReportType && in_array($requestedReportType, ['assy', 'inject', 'general'])) {
                $reportType = $requestedReportType;
            }
        }
        
        // Filter checksheets based on report type
        if ($reportType === 'assy') {
            // For assy department, only show checksheets with meja_id
            $recentChecksheetsQuery->whereNotNull('meja_id');
        } elseif ($reportType === 'inject') {
            // For inject department, only show checksheets with mesin_id
            $recentChecksheetsQuery->whereNotNull('mesin_id');
        }
        
        $recentChecksheets = $recentChecksheetsQuery->orderBy('created_at', 'desc')->limit(10)->get();
        
        // Get statistics by shift (apply same filters)
        $shiftStatsQuery = (clone $baseQuery);
        
        // Apply report type filter
        if ($reportType === 'assy') {
            $shiftStatsQuery->whereNotNull('meja_id');
        } elseif ($reportType === 'inject') {
            $shiftStatsQuery->whereNotNull('mesin_id');
        }
        
        $shiftStats = $shiftStatsQuery->select('shift', DB::raw('count(*) as total'))
            ->groupBy('shift')
            ->get();
        
        // Get data for filters
        $mesins = Mesin::all();
        $parts = Part::all();
        $users = User::with('grup')->get();
        $grups = Grup::all();
        $mejas = Meja::all();
        
        // Debug: Log data being passed to view
        Log::info('Data being passed to view', [
            'mesins_count' => $mesins->count(),
            'parts_count' => $parts->count(),
            'users_count' => $users->count(),
            'grups_count' => $grups->count(),
            'mejas_count' => $mejas->count(),
            'grups_data' => $grups->toArray(),
            'recent_checksheets_count' => $recentChecksheets->count(),
            'sample_checksheet_group' => $recentChecksheets->first() ? [
                'user_id' => $recentChecksheets->first()->user_id,
                'user_name' => $recentChecksheets->first()->user->nama ?? 'N/A',
                'group_id' => $recentChecksheets->first()->user->grup_id ?? 'N/A',
                'group_name' => $recentChecksheets->first()->user->grup->nama_grup ?? 'N/A'
            ] : 'No checksheets'
        ]);
        
        // Set department specific data based on report type
        if ($reportType === 'assy') {
            // Get meja statistics for assy department
            $departmentSpecificData = [
                'type' => 'meja',
                'data' => Meja::all(),
                'stats' => [
                    'total' => Meja::count(),
                    'aktif' => Meja::where('status', 'aktif')->count(),
                    'tidak_aktif' => Meja::where('status', 'tidak_aktif')->count(),
                ]
            ];
        } elseif ($reportType === 'inject') {
            // Get mesin statistics for inject department
            $departmentSpecificData = [
                'type' => 'mesin',
                'data' => Mesin::all(),
                'stats' => [
                    'total' => Mesin::count(),
                    'aktif' => Mesin::where('status', 'aktif')->count(),
                    'maintenance' => Mesin::where('status', 'maintenance')->count(),
                    'tidak_aktif' => Mesin::where('status', 'tidak aktif')->count(),
                ]
            ];
        }
        
        // Get machine/meja statistics based on report type (apply same filters)
        if ($reportType === 'assy') {
            $machineStatsQuery = (clone $baseQuery)->with('meja')->whereNotNull('meja_id');
        } else {
            $machineStatsQuery = (clone $baseQuery)->with('mesin')->whereNotNull('mesin_id');
        }
        
        $machineStats = $machineStatsQuery->select(
            $reportType === 'assy' ? 'meja_id' : 'mesin_id', 
            DB::raw('count(*) as total')
        )
        ->groupBy($reportType === 'assy' ? 'meja_id' : 'mesin_id')
        ->orderBy('total', 'desc')
        ->limit(5)
        ->get();
        
        // Get group statistics (apply same filters)
        $groupStatsQuery = DB::table('checksheets')
            ->join('users', 'checksheets.user_id', '=', 'users.id')
            ->join('grups', 'users.grup_id', '=', 'grups.id')
            ->whereNotNull('users.grup_id');
        
        // Apply group filter
        if (request('filter_grup_id')) {
            $groupStatsQuery->where('users.grup_id', request('filter_grup_id'));
        }
        
        // Apply report type filter
        if ($reportType === 'assy') {
            $groupStatsQuery->whereNotNull('checksheets.meja_id');
        } elseif ($reportType === 'inject') {
            $groupStatsQuery->whereNotNull('checksheets.mesin_id');
        }
        
        $groupStats = $groupStatsQuery->select('grups.id', 'grups.nama_grup', DB::raw('count(*) as total'))
            ->groupBy('grups.id', 'grups.nama_grup')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        return view('report.index', compact(
            'totalChecksheets',
            'pendingChecksheets',
            'approvedChecksheets',
            'rejectedChecksheets',
            'recentChecksheets',
            'shiftStats',
            'machineStats',
            'groupStats',
            'mesins',
            'parts',
            'users',
            'grups',
            'mejas',
            'reportType',
            'departmentSpecificData',
            'userDepartment'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mesins = Mesin::all();
        $parts = Part::all();
        $users = User::with('grup')->get();
        $mejas = Meja::all();
        $grups = Grup::all();
        
        return view('report.create', compact('mesins', 'parts', 'users', 'mejas', 'grups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $reportType = 'general';
        
        // Determine report type based on user role and department
        if ($user->role === 'leader') {
            // For leaders, report type is fixed to their department
            if (isset($user->bagian) && strtolower($user->bagian) === 'assy') {
                $reportType = 'assy';
            } elseif (isset($user->bagian) && strtolower($user->bagian) === 'inject') {
                $reportType = 'inject';
            }
            // Leaders cannot change their report type via request parameters
        } elseif (in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            // For super_admin, kabag, and manager_plant, check if report_type is specified in request
            if ($request->filled('report_type') && in_array($request->report_type, ['assy', 'inject', 'general'])) {
                $reportType = $request->report_type;
            }
        }
        
        // Check for department_type parameter (from modal forms) - only for non-leaders
        if ($user->role !== 'leader' && $request->filled('department_type') && in_array($request->department_type, ['assy', 'inject'])) {
            $reportType = $request->department_type;
        }
        
        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'grup_id' => 'nullable|exists:grups,id',
            'shift' => 'nullable|in:pagi,siang,malam',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'photo_data' => 'nullable|string',
            'photo_description' => 'nullable|string|max:500',
        ];
        
        // Add conditional validation based on report type
        if ($reportType === 'assy') {
            $validationRules['meja_id'] = 'nullable|exists:meja,id_meja';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        } else {
            $validationRules['mesin_id'] = 'nullable|exists:mesin,id';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        }

        // Debug: Log request data
        Log::info('Report request data', $request->all());
        Log::info('Validation rules', $validationRules);
        Log::info('User role', ['role' => $user->role]);
        Log::info('Report type', ['type' => $reportType]);

        $request->validate($validationRules);

        try {
            $query = Checksheet::with(['mesin', 'part', 'user.grup', 'meja', 'approver']);

            // Debug: Log request data
            Log::info('Report request data', $request->all());

            // Apply department-specific filters for leaders
            if ($user->role === 'leader') {
                if ($reportType === 'assy') {
                    // For assy department, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject department, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            } else {
                // For super_admin, kabag, and manager_plant, apply filters based on report_type
                if ($reportType === 'assy') {
                    // For assy report type, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject report type, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            }

            // Apply filters based on report type
            if ($reportType === 'assy') {
                if ($request->filled('meja_id')) {
                    $query->where('meja_id', $request->meja_id);
                }
            } else {
                if ($request->filled('mesin_id')) {
                    $query->where('mesin_id', $request->mesin_id);
                }
            }
            
            if ($request->filled('part_id')) {
                $query->where('part_id', $request->part_id);
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Apply group filter
            if ($request->filled('grup_id')) {
                Log::info('Applying group filter for grup_id', ['grup_id' => $request->grup_id]);
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('grup_id', $request->grup_id);
                });
            }
            
            if ($request->filled('shift')) {
                $query->where('shift', $request->shift);
            }
            
            if ($request->filled('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            // Apply date range
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

            $checksheets = $query->orderBy('created_at', 'desc')->get();
            
            // Debug: Log query results
            Log::info('Query results count', ['count' => $checksheets->count()]);
            Log::info('Report type', ['type' => $reportType]);

            // Handle photo data if provided
            $photoPath = null;
            if ($request->filled('photo_data')) {
                $photoData = $request->photo_data;
                
                // Remove data:image/jpeg;base64, prefix if present
                if (strpos($photoData, 'data:image/jpeg;base64,') === 0) {
                    $photoData = substr($photoData, 23);
                } elseif (strpos($photoData, 'data:image/png;base64,') === 0) {
                    $photoData = substr($photoData, 22);
                }
                
                // Decode base64 and save to file
                $imageData = base64_decode($photoData);
                $fileName = 'report_photo_' . time() . '.jpg';
                $path = 'report-photos/' . $fileName;
                
                Storage::disk('public')->put($path, $imageData);
                $photoPath = $path;
            }

            // Generate report data
            $reportData = [
                'filters' => $request->all(),
                'total_records' => $checksheets->count(),
                'data' => $checksheets,
                'summary' => $this->generateSummary($checksheets),
                'photo_path' => $photoPath,
                'photo_description' => $request->photo_description,
                'generated_at' => now(),
                'generated_by' => auth()->user()->nama ?? 'System',
                'report_type' => $reportType
            ];

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Laporan berhasil dibuat',
                    'data' => $reportData
                ]);
            }

            return view('report.show', compact('reportData'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat laporan')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // This method can be used to show a specific report
        return view('report.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // This method can be used to edit report parameters
        return view('report.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // This method can be used to update report parameters
        return redirect()->route('report.index')
            ->with('success', 'Laporan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // This method can be used to delete saved reports
        return redirect()->route('report.index')
            ->with('success', 'Laporan berhasil dihapus');
    }

    /**
     * Generate summary statistics for the report
     */
    private function generateSummary($checksheets)
    {
        $summary = [
            'total' => $checksheets->count(),
            'by_status' => [
                'pending' => $checksheets->where('approval_status', 'pending')->count(),
                'approved' => $checksheets->where('approval_status', 'approved')->count(),
                'rejected' => $checksheets->where('approval_status', 'rejected')->count(),
            ],
            'by_shift' => [
                'pagi' => $checksheets->where('shift', 'pagi')->count(),
                'siang' => $checksheets->where('shift', 'siang')->count(),
                'malam' => $checksheets->where('shift', 'malam')->count(),
            ],
            'by_machine' => $checksheets->groupBy('mesin_id')->map->count(),
            'by_part' => $checksheets->groupBy('part_id')->map->count(),
            'by_user' => $checksheets->groupBy('user_id')->map->count(),
        ];

        return $summary;
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $reportType = 'general';
        
        // Determine report type based on user role and department
        if ($user->role === 'leader') {
            // For leaders, report type is fixed to their department
            if (isset($user->bagian) && strtolower($user->bagian) === 'assy') {
                $reportType = 'assy';
            } elseif (isset($user->bagian) && strtolower($user->bagian) === 'inject') {
                $reportType = 'inject';
            }
            // Leaders cannot change their report type via request parameters
        } elseif (in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            // For super_admin, kabag, and manager_plant, check if report_type is specified in request
            if ($request->filled('report_type') && in_array($request->report_type, ['assy', 'inject', 'general'])) {
                $reportType = $request->report_type;
            }
        }
        
        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'grup_id' => 'nullable|exists:grups,id',
            'shift' => 'nullable|in:pagi,siang,malam',
            'approval_status' => 'nullable|in:pending,approved,rejected',
        ];
        
        // Only add report_type validation for non-leaders
        if ($user->role !== 'leader') {
            $validationRules['report_type'] = 'nullable|in:assy,inject,general';
        }
        
        // Add conditional validation based on report type
        if ($reportType === 'assy') {
            $validationRules['meja_id'] = 'nullable|exists:meja,id_meja';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        } else {
            $validationRules['mesin_id'] = 'nullable|exists:mesin,id';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        }

        $request->validate($validationRules);

        try {
            $query = Checksheet::with(['mesin', 'part', 'user.grup', 'meja', 'approver']);

            // Apply department-specific filters for leaders
            if ($user->role === 'leader') {
                if ($reportType === 'assy') {
                    // For assy department, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject department, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            } else {
                // For super_admin, kabag, and manager_plant, apply filters based on report_type
                if ($reportType === 'assy') {
                    // For assy report type, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject report type, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            }

            // Apply filters based on report type
            if ($reportType === 'assy') {
                if ($request->filled('meja_id')) {
                    $query->where('meja_id', $request->meja_id);
                }
            } else {
                if ($request->filled('mesin_id')) {
                    $query->where('mesin_id', $request->mesin_id);
                }
            }
            
            if ($request->filled('part_id')) {
                $query->where('part_id', $request->part_id);
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Apply group filter
            if ($request->filled('grup_id')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('grup_id', $request->grup_id);
                });
            }
            
            if ($request->filled('shift')) {
                $query->where('shift', $request->shift);
            }
            
            if ($request->filled('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            // Apply date range
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

            $checksheets = $query->orderBy('created_at', 'desc')->get();

            // Generate summary for the report
            $summary = $this->generateSummary($checksheets);

            $fileName = 'laporan_checksheet_' . $reportType . '_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new ChecksheetExport($checksheets, $request->all(), $reportType, $summary), $fileName);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengekspor Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $reportType = 'general';
        
        // Determine report type based on user role and department
        if ($user->role === 'leader') {
            // For leaders, report type is fixed to their department
            if (isset($user->bagian) && strtolower($user->bagian) === 'assy') {
                $reportType = 'assy';
            } elseif (isset($user->bagian) && strtolower($user->bagian) === 'inject') {
                $reportType = 'inject';
            }
            // Leaders cannot change their report type via request parameters
        } elseif (in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            // For super_admin, kabag, and manager_plant, check if report_type is specified in request
            if ($request->filled('report_type') && in_array($request->report_type, ['assy', 'inject', 'general'])) {
                $reportType = $request->report_type;
            }
        }
        
        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'grup_id' => 'nullable|exists:grups,id',
            'shift' => 'nullable|in:pagi,siang,malam',
            'approval_status' => 'nullable|in:pending,approved,rejected',
        ];
        
        // Only add report_type validation for non-leaders
        if ($user->role !== 'leader') {
            $validationRules['report_type'] = 'nullable|in:assy,inject,general';
        }
        
        // Add conditional validation based on report type
        if ($reportType === 'assy') {
            $validationRules['meja_id'] = 'nullable|exists:meja,id_meja';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        } else {
            $validationRules['mesin_id'] = 'nullable|exists:mesin,id';
            $validationRules['part_id'] = 'nullable|exists:parts,id';
        }

        $request->validate($validationRules);

        try {
            $query = Checksheet::with(['mesin', 'part', 'user.grup', 'meja', 'approver']);

            // Apply department-specific filters for leaders
            if ($user->role === 'leader') {
                if ($reportType === 'assy') {
                    // For assy department, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject department, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            } else {
                // For super_admin, kabag, and manager_plant, apply filters based on report_type
                if ($reportType === 'assy') {
                    // For assy report type, only show checksheets with meja_id
                    $query->whereNotNull('meja_id');
                } elseif ($reportType === 'inject') {
                    // For inject report type, only show checksheets with mesin_id
                    $query->whereNotNull('mesin_id');
                }
            }

            // Apply filters based on report type
            if ($reportType === 'assy') {
                if ($request->filled('meja_id')) {
                    $query->where('meja_id', $request->meja_id);
                }
            } else {
                if ($request->filled('mesin_id')) {
                    $query->where('mesin_id', $request->mesin_id);
                }
            }
            
            if ($request->filled('part_id')) {
                $query->where('part_id', $request->part_id);
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Apply group filter
            if ($request->filled('grup_id')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('grup_id', $request->grup_id);
                });
            }
            
            if ($request->filled('shift')) {
                $query->where('shift', $request->shift);
            }
            
            if ($request->filled('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            // Apply date range
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

            $checksheets = $query->orderBy('created_at', 'desc')->get();

            // Generate report data
            $reportData = [
                'filters' => $request->all(),
                'total_records' => $checksheets->count(),
                'data' => $checksheets,
                'summary' => $this->generateSummary($checksheets),
                'photo_path' => null,
                'photo_description' => null,
                'generated_at' => now(),
                'generated_by' => auth()->user()->nama ?? 'System',
                'report_type' => $reportType
            ];

            $fileName = 'laporan_checksheet_' . date('Y-m-d_H-i-s') . '.pdf';

            // Generate PDF using DomPDF
            $html = view('report.pdf', compact('reportData'))->render();
            
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response()->make($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report to Excel/PDF (legacy method for backward compatibility)
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,pdf',
            'report_data' => 'required|array'
        ]);

        $reportData = $request->report_data;
        
        try {
            if ($request->format === 'excel') {
                return $this->exportExcel(new Request($reportData));
            } else {
                return $this->exportPdf(new Request($reportData));
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengekspor: ' . $e->getMessage()
            ], 500);
        }
    }
} 