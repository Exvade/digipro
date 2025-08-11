<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use App\Models\Mesin;
use App\Models\DowntimeEvent;
use App\Models\DowntimeCategory;
use App\Models\QualityEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionController extends Controller
{
    public function downtime(Request $request)
    {
        $machines = Mesin::all();
        $categories = DowntimeCategory::active()->ordered()->get();
        
        $downtimeEvents = DowntimeEvent::with(['mesin', 'category', 'code'])
            ->when($request->mesin_id, function($query, $mesinId) {
                return $query->where('mesin_id', $mesinId);
            })
            ->when($request->category_id, function($query, $categoryId) {
                return $query->where('downtime_category_id', $categoryId);
            })
            ->when($request->period, function($query, $period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('oee.production.downtime', compact('machines', 'categories', 'downtimeEvents'));
    }
    
    public function getDowntimeData(Request $request)
    {
        $query = DowntimeEvent::with(['mesin', 'category']);
        
        // Apply filters
        if ($request->mesin_id) {
            $query->where('mesin_id', $request->mesin_id);
        }
        
        if ($request->category_id) {
            $query->where('downtime_category_id', $request->category_id);
        }
        
        $this->applyPeriodFilter($query, $request->period ?? 'month');
        
        $events = $query->get();
        
        // Calculate summary
        $summary = [
            'total_downtime' => round($events->sum('duration_minutes') / 60, 2),
            'total_events' => $events->count(),
            'avg_duration' => $events->count() > 0 ? round($events->avg('duration_minutes'), 2) : 0,
            'resolved_events' => $events->where('status', 'resolved')->count()
        ];
        
        // Prepare chart data
        $categoryData = $events->groupBy('downtime_category_id')
            ->map(function($group) {
                return $group->sum('duration_minutes');
            });
            
        $machineData = $events->groupBy('mesin_id')
            ->map(function($group) {
                return $group->sum('duration_minutes');
            });
            
        $trendData = $events->groupBy('tanggal')
            ->map(function($group) {
                return $group->sum('duration_minutes');
            });
        
        return response()->json([
            'summary' => $summary,
            'charts' => [
                'category' => [
                    'labels' => $categoryData->keys()->map(fn($id) => DowntimeCategory::find($id)->name ?? 'Unknown'),
                    'data' => $categoryData->values()
                ],
                'machine' => [
                    'labels' => $machineData->keys()->map(fn($id) => Mesin::find($id)->nama_mesin ?? 'Unknown'),
                    'data' => $machineData->values()
                ],
                'trend' => [
                    'labels' => $trendData->keys()->map(fn($date) => $date->format('d/m')),
                    'data' => $trendData->values()
                ]
            ]
        ]);
    }
    
    public function exportDowntime(Request $request)
    {
        $query = DowntimeEvent::with(['mesin', 'category', 'code']);
        
        // Apply filters
        if ($request->mesin_id) {
            $query->where('mesin_id', $request->mesin_id);
        }
        
        if ($request->category_id) {
            $query->where('downtime_category_id', $request->category_id);
        }
        
        $this->applyPeriodFilter($query, $request->period ?? 'month');
        
        $events = $query->get();
        
        // Implementation for Excel export
        // You can use Maatwebsite\Excel package
        
        return response()->json([
            'success' => true,
            'message' => 'Export berhasil',
            'data' => $events
        ]);
    }
    
    public function quality(Request $request)
    {
        $machines = Mesin::all();
        
        $qualityEvents = QualityEvent::with(['mesin'])
            ->when($request->mesin_id, function($query, $mesinId) {
                return $query->where('mesin_id', $mesinId);
            })
            ->when($request->period, function($query, $period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('oee.production.quality', compact('machines', 'qualityEvents'));
    }
    
    public function performance(Request $request)
    {
        $machines = Mesin::all();
        
        // Implementation for performance analysis
        return view('oee.production.performance', compact('machines'));
    }
    
    public function shifts(Request $request)
    {
        $machines = Mesin::all();
        
        // Implementation for shift analysis
        return view('oee.production.shifts', compact('machines'));
    }
    
    private function applyPeriodFilter($query, $period)
    {
        switch ($period) {
            case 'today':
                return $query->where('tanggal', Carbon::today());
            case 'week':
                return $query->whereBetween('tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            case 'month':
                return $query->whereBetween('tanggal', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
            case 'custom':
                // Handle custom date range
                return $query;
            default:
                return $query->whereBetween('tanggal', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
        }
    }
} 