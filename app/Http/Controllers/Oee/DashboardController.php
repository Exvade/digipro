<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use App\Models\Mesin;
use App\Models\OeeData;
use App\Models\DowntimeEvent;
use App\Models\QualityEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index()
    {
        // Get current date
        $today = Carbon::today();
        
        // Get machines with their current OEE data
        $machines = Mesin::with(['oeeData' => function($query) use ($today) {
            $query->where('tanggal', $today);
        }])->get();
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($today);
        
        // Get recent events
        $recentDowntime = DowntimeEvent::with(['mesin', 'category', 'code'])
            ->where('tanggal', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentQuality = QualityEvent::with(['mesin'])
            ->where('tanggal', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('oee.dashboard.index', compact('machines', 'summary', 'recentDowntime', 'recentQuality'));
    }

    public function getMachineStatus(): JsonResponse
    {
        $today = Carbon::today();
        
        // Count machines by status - using correct enum values from migration
        $activeCount = Mesin::where('status', 'aktif')->count();
        $maintenanceCount = Mesin::where('status', 'maintenance')->count();
        $downtimeCount = DowntimeEvent::where('tanggal', $today)
            ->where('status', 'active')
            ->count();
        
        // Calculate average OEE
        $avgOee = OeeData::where('tanggal', $today)->avg('oee') ?? 0;
        
        return response()->json([
            'active_count' => $activeCount,
            'maintenance_count' => $maintenanceCount,
            'downtime_count' => $downtimeCount,
            'avg_oee' => round($avgOee, 2)
        ]);
    }

    public function getRecentEvents(): JsonResponse
    {
        $today = Carbon::today();
        
        $downtimeEvents = DowntimeEvent::with(['mesin', 'category'])
            ->where('tanggal', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($event) {
                return [
                    'mesin' => [
                        'nama_mesin' => $event->mesin->nama_mesin ?? 'N/A'
                    ],
                    'category' => [
                        'name' => $event->category->name ?? 'N/A'
                    ],
                    'duration_minutes' => $event->duration ?? 0,
                    'status' => $event->status ?? 'closed'
                ];
            });
            
        $qualityEvents = QualityEvent::with(['mesin'])
            ->where('tanggal', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($event) {
                return [
                    'mesin' => [
                        'nama_mesin' => $event->mesin->nama_mesin ?? 'N/A'
                    ],
                    'defect_type' => $event->defect_type ?? 'N/A',
                    'quantity_defective' => $event->quantity ?? 0,
                    'status' => $event->status ?? 'closed'
                ];
            });
        
        return response()->json([
            'downtime_events' => $downtimeEvents,
            'quality_events' => $qualityEvents
        ]);
    }

    public function getChartData(): JsonResponse
    {
        $today = Carbon::today();
        
        // Generate time labels for today (6 AM to 6 PM)
        $labels = [];
        for ($hour = 6; $hour <= 18; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
        }
        
        // Mock data for charts (replace with real data from database)
        $oeeTrend = [
            'labels' => $labels,
            'data' => [85, 87, 82, 89, 91, 88, 86, 90, 92, 89, 87, 85, 88]
        ];
        
        $downtimeDistribution = [
            'labels' => ['Mechanical', 'Electrical', 'Setup', 'Material', 'Other'],
            'data' => [30, 25, 20, 15, 10]
        ];
        
        $availabilityTrend = [
            'labels' => $labels,
            'data' => [90, 92, 88, 95, 93, 91, 89, 94, 96, 92, 90, 88, 91]
        ];
        
        $performanceTrend = [
            'labels' => $labels,
            'data' => [88, 90, 85, 92, 94, 91, 89, 93, 95, 91, 89, 87, 90]
        ];
        
        $qualityTrend = [
            'labels' => $labels,
            'data' => [95, 96, 94, 97, 98, 96, 95, 97, 99, 96, 95, 94, 96]
        ];
        
        return response()->json([
            'oee_trend' => $oeeTrend,
            'downtime_distribution' => $downtimeDistribution,
            'availability_trend' => $availabilityTrend,
            'performance_trend' => $performanceTrend,
            'quality_trend' => $qualityTrend
        ]);
    }
    
    private function calculateSummary($date)
    {
        $oeeData = OeeData::where('tanggal', $date)->get();
        
        if ($oeeData->isEmpty()) {
            return [
                'oee_year' => 0,
                'availability' => 0,
                'performance' => 0,
                'quality' => 0,
                'ng_product' => 0,
                'ng_process' => 0,
                'total_downtime' => 0,
                'running_time' => 0
            ];
        }
        
        return [
            'oee_year' => round($oeeData->avg('oee'), 2),
            'availability' => round($oeeData->avg('availability'), 2),
            'performance' => round($oeeData->avg('performance'), 2),
            'quality' => round($oeeData->avg('quality'), 2),
            'ng_product' => round($oeeData->avg('ng_product') ?? 0, 2),
            'ng_process' => round($oeeData->avg('ng_process') ?? 0, 2),
            'total_downtime' => $oeeData->sum('total_downtime'),
            'running_time' => $oeeData->sum('running_time')
        ];
    }
} 