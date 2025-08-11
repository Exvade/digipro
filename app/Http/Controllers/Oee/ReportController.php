<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use App\Models\Mesin;
use App\Models\OeeData;
use App\Models\DowntimeEvent;
use App\Models\QualityEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        // Redirect to dashboard since reports are handled by leader controller
        return redirect()->route('oee.dashboard.index')->with('info', 'Laporan harian tersedia di dashboard leader');
    }
    
    public function getDailyData(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $mesinId = $request->mesin_id;
        $shift = $request->shift;
        
        $query = OeeData::with(['mesin'])
            ->where('tanggal', $date);
            
        if ($mesinId) {
            $query->where('mesin_id', $mesinId);
        }
        
        if ($shift) {
            $query->where('shift', $shift);
        }
        
        $oeeData = $query->get();
        
        // Calculate summary
        $summary = $this->calculateDailySummary($oeeData);
        
        // Prepare chart data
        $charts = $this->prepareDailyCharts($date, $mesinId, $shift);
        
        // Prepare table data
        $tables = $this->prepareDailyTables($date, $mesinId, $shift);
        
        return response()->json([
            'summary' => $summary,
            'charts' => $charts,
            'tables' => $tables
        ]);
    }
    
    public function exportDaily(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $mesinId = $request->mesin_id;
        $shift = $request->shift;
        
        $query = OeeData::with(['mesin'])
            ->where('tanggal', $date);
            
        if ($mesinId) {
            $query->where('mesin_id', $mesinId);
        }
        
        if ($shift) {
            $query->where('shift', $shift);
        }
        
        $oeeData = $query->get();
        
        // Implementation for PDF export
        // You can use DomPDF or similar package
        
        return response()->json([
            'success' => true,
            'message' => 'Export PDF berhasil',
            'data' => $oeeData
        ]);
    }
    
    public function weekly(Request $request)
    {
        // Redirect to dashboard since reports are handled by leader controller
        return redirect()->route('oee.dashboard.index')->with('info', 'Laporan mingguan tersedia di dashboard leader');
    }
    
    public function monthly(Request $request)
    {
        // Redirect to dashboard since reports are handled by leader controller
        return redirect()->route('oee.dashboard.index')->with('info', 'Laporan bulanan tersedia di dashboard leader');
    }
    
    public function comparison(Request $request)
    {
        // Redirect to dashboard since reports are handled by leader controller
        return redirect()->route('oee.dashboard.index')->with('info', 'Laporan perbandingan tersedia di dashboard leader');
    }
    
    private function calculateDailySummary($oeeData)
    {
        if ($oeeData->isEmpty()) {
            return [
                'avg_oee' => 0,
                'avg_availability' => 0,
                'avg_performance' => 0,
                'avg_quality' => 0,
                'total_units' => 0,
                'good_units' => 0,
                'rejected_units' => 0,
                'total_downtime' => 0,
                'total_stops' => 0
            ];
        }
        
        return [
            'avg_oee' => round($oeeData->avg('oee'), 2),
            'avg_availability' => round($oeeData->avg('availability'), 2),
            'avg_performance' => round($oeeData->avg('performance'), 2),
            'avg_quality' => round($oeeData->avg('quality'), 2),
            'total_units' => $oeeData->sum('total_units_produced'),
            'good_units' => $oeeData->sum('good_units_produced'),
            'rejected_units' => $oeeData->sum('rejected_units'),
            'total_downtime' => $oeeData->sum('total_downtime'),
            'total_stops' => $oeeData->sum('stop_count')
        ];
    }
    
    private function prepareDailyCharts($date, $mesinId, $shift)
    {
        // OEE Distribution
        $oeeDistribution = $this->getOeeDistribution($date, $mesinId, $shift);
        
        // Downtime by Category
        $downtimeCategory = $this->getDowntimeByCategory($date, $mesinId, $shift);
        
        // Quality Issues
        $qualityIssues = $this->getQualityIssues($date, $mesinId, $shift);
        
        return [
            'oee_distribution' => $oeeDistribution,
            'downtime_category' => $downtimeCategory,
            'quality_issues' => $qualityIssues
        ];
    }
    
    private function prepareDailyTables($date, $mesinId, $shift)
    {
        // Machine Performance
        $machinePerformance = OeeData::with(['mesin'])
            ->where('tanggal', $date)
            ->when($mesinId, function($query, $id) {
                return $query->where('mesin_id', $id);
            })
            ->when($shift, function($query, $s) {
                return $query->where('shift', $s);
            })
            ->get()
            ->map(function($data) {
                return [
                    'mesin' => $data->mesin->nama_mesin,
                    'shift' => $data->shift,
                    'availability' => $data->availability,
                    'availability_color' => $data->availability_color,
                    'performance' => $data->performance,
                    'performance_color' => $data->performance_color,
                    'quality' => $data->quality,
                    'quality_color' => $data->quality_color,
                    'oee' => $data->oee,
                    'oee_color' => $data->oee_color,
                    'units_produced' => $data->total_units_produced,
                    'downtime' => $data->total_downtime,
                    'status' => $data->oee >= 85 ? 'Excellent' : ($data->oee >= 70 ? 'Good' : 'Poor'),
                    'status_color' => $data->oee >= 85 ? 'success' : ($data->oee >= 70 ? 'warning' : 'danger')
                ];
            });
        
        // Downtime Events
        $downtimeEvents = DowntimeEvent::with(['mesin', 'category'])
            ->where('tanggal', $date)
            ->when($mesinId, function($query, $id) {
                return $query->where('mesin_id', $id);
            })
            ->when($shift, function($query, $s) {
                return $query->where('shift', $s);
            })
            ->get()
            ->map(function($event) {
                return [
                    'time' => $event->start_time,
                    'machine' => $event->mesin->nama_mesin,
                    'category' => $event->category->name,
                    'duration' => $event->duration_minutes
                ];
            });
        
        // Quality Events
        $qualityEvents = QualityEvent::with(['mesin'])
            ->where('tanggal', $date)
            ->when($mesinId, function($query, $id) {
                return $query->where('mesin_id', $id);
            })
            ->when($shift, function($query, $s) {
                return $query->where('shift', $s);
            })
            ->get()
            ->map(function($event) {
                return [
                    'time' => $event->time_occurred,
                    'machine' => $event->mesin->nama_mesin,
                    'defect_type' => $event->defect_type,
                    'quantity' => $event->quantity_defective
                ];
            });
        
        return [
            'machine_performance' => $machinePerformance,
            'downtime_events' => $downtimeEvents,
            'quality_events' => $qualityEvents
        ];
    }
    
    private function getOeeDistribution($date, $mesinId, $shift)
    {
        $query = OeeData::where('tanggal', $date);
        
        if ($mesinId) {
            $query->where('mesin_id', $mesinId);
        }
        
        if ($shift) {
            $query->where('shift', $shift);
        }
        
        $data = $query->get();
        
        $excellent = $data->where('oee', '>=', 85)->count();
        $good = $data->whereBetween('oee', [70, 84.99])->count();
        $poor = $data->where('oee', '<', 70)->count();
        
        return [$excellent, $good, $poor];
    }
    
    private function getDowntimeByCategory($date, $mesinId, $shift)
    {
        $query = DowntimeEvent::with('category')
            ->where('tanggal', $date);
            
        if ($mesinId) {
            $query->where('mesin_id', $mesinId);
        }
        
        if ($shift) {
            $query->where('shift', $shift);
        }
        
        $events = $query->get();
        
        $categoryData = $events->groupBy('downtime_category_id')
            ->map(function($group) {
                return $group->sum('duration_minutes');
            });
        
        return [
            'labels' => $categoryData->keys()->map(fn($id) => \App\Models\DowntimeCategory::find($id)->name ?? 'Unknown'),
            'data' => $categoryData->values()
        ];
    }
    
    private function getQualityIssues($date, $mesinId, $shift)
    {
        $query = QualityEvent::where('tanggal', $date);
        
        if ($mesinId) {
            $query->where('mesin_id', $mesinId);
        }
        
        if ($shift) {
            $query->where('shift', $shift);
        }
        
        $events = $query->get();
        
        $defectData = $events->groupBy('defect_type')
            ->map(function($group) {
                return $group->sum('quantity_defective');
            });
        
        return [
            'labels' => $defectData->keys(),
            'data' => $defectData->values()
        ];
    }
} 