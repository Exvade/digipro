<?php

namespace App\Http\Controllers;

// OeeRecord model tidak digunakan di dashboard - dashboard menghitung real-time
// use App\Models\OeeRecord;
use App\Models\Mesin;
use App\Models\Meja;
use App\Models\PlanningProduksi;
use App\Models\InputProduksi;
use App\Models\Ploting;
// OEEService tidak digunakan - semua perhitungan dilakukan langsung di controller
// use App\Services\OEEService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\OeeExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class OeeRecordController extends Controller
{
    // OEEService tidak digunakan - semua perhitungan dilakukan langsung di controller
    // protected $oeeService;
    // 
    // public function __construct(OEEService $oeeService)
    // {
    //     $this->oeeService = $oeeService;
    // }

    /**
     * Dashboard OEE - Using real data from InputProduksi and PlanningProduksi
     */
    public function dashboard(Request $request)
    {
        // Base query for planning with input produksi - HANYA yang sudah approved
        $query = PlanningProduksi::with(['mesin', 'meja', 'part', 'inputProduksi.ngPartTypes', 'plotings'])
            ->whereHas('inputProduksi', function ($q) {
                $q->where('status', 'approved'); // Hanya data yang sudah approved
            }); // Only planning with approved input produksi

        // Handle period filtering
        $period = $request->get('period', 'daily');
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Custom date range
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
            // Period-based filtering
            switch ($period) {
                case '7days':
                    $startDate = now()->subDays(6)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case '30days':
                    $startDate = now()->subDays(29)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case 'daily':
                    $startDate = now()->startOfDay()->toDateString();
                    $endDate = now()->endOfDay()->toDateString();
                    $query->whereDate('tanggal', now()->toDateString());
                    break;
                case 'month':
                default:
                    $startDate = now()->startOfMonth()->toDateString();
                    $endDate = now()->endOfMonth()->toDateString();
                    $query->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year);
                    break;
            }
        }

        // Filter by bagian
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        // Filter by mesin (only for inject)
        if ($request->filled('mesin_id')) {
            $query->where('mesin_id', $request->mesin_id);
        }

        // Filter by meja (only for assy)
        if ($request->filled('meja_id')) {
            $query->where('meja_id', $request->meja_id);
        }

        // Get ALL planning data (without pagination) for totals calculation
        $allPlanningData = (clone $query)->orderBy('tanggal', 'desc')->get();

        // Debug: Log the first planning to check if it has plotings loaded
        if ($allPlanningData->count() > 0) {
            Log::info('First planning data check', [
                'planning_id' => $allPlanningData->first()->id,
                'has_plotings' => $allPlanningData->first()->relationLoaded('plotings'),
                'plotings_count' => $allPlanningData->first()->plotings->count(),
                'has_input_produksi' => $allPlanningData->first()->relationLoaded('inputProduksi'),
            ]);
        }

        $allOeeData = $allPlanningData->map(function ($planning) {
            return $this->calculateOeeForPlanning($planning);
        });

        // Totals for summary info (from ALL filtered data, not just current page)
        $totalPlannedMinutes = 0;
        $totalRunMinutes = 0;
        $totalDownMinutes = 0;
        $totalOutputOK = 0;
        $totalOutputNG = 0;

        foreach ($allOeeData as $item) {
            $planned = property_exists($item, 'planned_time') ? $item->planned_time : 0;
            $run = property_exists($item, 'run_time') ? $item->run_time : 0;
            $goodOutput = property_exists($item, 'good_output') ? $item->good_output : 0;
            $ngOutput = property_exists($item, 'ng_output') ? $item->ng_output : 0;

            $totalPlannedMinutes += (int) $planned;
            $totalRunMinutes += (int) $run;
            $totalOutputOK += (int) $goodOutput;
            $totalOutputNG += (int) $ngOutput;
        }

        $totalDownMinutes = max(0, $totalPlannedMinutes - $totalRunMinutes);

        Log::info('OEE Totals Calculation', [
            'allOeeData_count' => $allOeeData->count(),
            'totalPlannedMinutes' => $totalPlannedMinutes,
            'totalRunMinutes' => $totalRunMinutes,
            'totalDownMinutes' => $totalDownMinutes,
            'totalOutputOK' => $totalOutputOK,
            'totalOutputNG' => $totalOutputNG,
            'first_item' => $allOeeData->first() ? get_object_vars($allOeeData->first()) : 'no data'
        ]);

        // Calculate averages from ALL data (not just current page)
        $avgOEE = $allOeeData->avg('oee_rate') ?? 0;
        $avgAvailability = $allOeeData->avg('availability_rate') ?? 0;
        $avgPerformance = $allOeeData->avg('performance_rate') ?? 0;
        $avgQuality = $allOeeData->avg('quality_rate') ?? 0;

        // Get planning data for pagination (for table display)
        $planningData = $query->orderBy('tanggal', 'desc')->paginate(20);

        // Calculate OEE metrics for each planning (for current page only)
        $oeeData = $planningData->getCollection()->map(function ($planning) {
            return $this->calculateOeeForPlanning($planning);
        });

        // Get all mesins and mejas for filter
        $mesins = Mesin::orderBy('nama_mesin')->get();
        $mejas = Meja::orderBy('nama_meja')->get();

        // Prepare chart data based on period (using ALL filtered data, not just current page)
        $chartData = $this->prepareChartDataFromPlanning($allOeeData, $period, $startDate, $endDate);

        // Debug information
        Log::info('OEE Dashboard Data', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'oeeDataCount' => $allOeeData->count(),
            'totalPlannedMinutes' => $totalPlannedMinutes,
            'totalRunMinutes' => $totalRunMinutes,
            'totalDownMinutes' => $totalDownMinutes,
            'sample_item' => $allOeeData->first() ? [
                'planned_time' => $allOeeData->first()->planned_time ?? 'not set',
                'run_time' => $allOeeData->first()->run_time ?? 'not set'
            ] : 'no data',
            'chartData' => $chartData
        ]);

        // Create a new paginator with the calculated OEE data
        $paginatedOeeData = new \Illuminate\Pagination\LengthAwarePaginator(
            $oeeData,
            $planningData->total(),
            $planningData->perPage(),
            $planningData->currentPage(),
            [
                'path' => $planningData->path(),
                'pageName' => $planningData->getPageName(),
            ]
        );

        // Handle AJAX requests for period changes & realtime updates
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'avgOEE' => $avgOEE,
                'avgAvailability' => $avgAvailability,
                'avgPerformance' => $avgPerformance,
                'avgQuality' => $avgQuality,
                'totalPlannedMinutes' => $totalPlannedMinutes,
                'totalRunMinutes' => $totalRunMinutes,
                'totalDownMinutes' => $totalDownMinutes,
                'totalOutputOK' => $totalOutputOK,
                'totalOutputNG' => $totalOutputNG,
                'chartData' => $chartData,
                'oeeData' => $oeeData,
                'period' => $period,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        }

        return view('oee.index', compact(
            'avgOEE',
            'avgAvailability',
            'avgPerformance',
            'avgQuality',
            'mesins',
            'mejas',
            'chartData',
            'period',
            'startDate',
            'endDate',
            'totalPlannedMinutes',
            'totalRunMinutes',
            'totalDownMinutes',
            'totalOutputOK',
            'totalOutputNG'
        ))->with('oeeData', $paginatedOeeData);
    }

    /**
     * Calculate OEE metrics for a planning record
     */
    private function calculateOeeForPlanning($planning)
    {
        $inputProduksi = $planning->inputProduksi;

        if (!$inputProduksi) {
            // Get BOM Details for cycle time and cavity mold
            $bomDetail = $planning->bomDetail;
            $cycleTimeStd = $bomDetail ? $bomDetail->cycle_time_std : 60;
            $cavityMold = $bomDetail ? $bomDetail->cavity_mold : 1;

            return (object) [
                'id' => $planning->id,
                'tanggal' => Carbon::parse($planning->tanggal),
                'mesin' => $planning->mesin,
                'planning' => $planning,
                'actual_output' => 0,
                'good_output' => 0,
                'ng_output' => 0,
                'planned_time' => 480,
                'run_time' => 0,
                'cycle_time_std' => $cycleTimeStd,
                'cavity_mold' => $cavityMold,
                'cycle_time_actual' => 60,
                'ng_material' => 0,
                'ng_process' => 0,
                'ng_machine' => 0,
                'ng_operator' => 0,
                'ng_other' => 0,
                'ng_details' => collect(),
                'availability_rate' => 0,
                'performance_rate' => 0,
                'quality_rate' => 0,
                'oee_rate' => 0,
                'availability_color' => 'danger',
                'performance_color' => 'danger',
                'quality_color' => 'danger',
                'oee_color' => 'danger'
            ];
        }

        // Calculate actual output (OK + NG)
        $actualOutput = ($inputProduksi->ok_count ?? 0) + ($inputProduksi->ng_count ?? 0);
        $goodOutput = $inputProduksi->ok_count ?? 0;
        $ngOutput = $inputProduksi->ng_count ?? 0;

        // Calculate time metrics (assuming 8-hour shift = 480 minutes)
        $plannedTime = 480; // 8 hours in minutes
        $runTime = $this->calculateRunTime($planning); // in minutes

        // Get BOM Details for cycle time and cavity mold
        $bomDetail = $planning->bomDetail;
        $cycleTimeStd = $bomDetail ? $bomDetail->cycle_time_std : 60; // seconds
        $cavityMold = $bomDetail ? $bomDetail->cavity_mold : 1;
        $cycleTimeActual = $this->calculateActualCycleTime($planning, $actualOutput);

        // Debug info (remove in production)
        // \Log::info('OEE Calculation Debug', [
        //     'planning_id' => $planning->id,
        //     'actual_output' => $actualOutput,
        //     'run_time_minutes' => $runTime,
        //     'cycle_time_std_seconds' => $cycleTimeStd,
        //     'cavity_mold' => $cavityMold,
        //     'cycle_time_actual_seconds' => $cycleTimeActual,
        //     'performance_before_cap' => $performance
        // ]);

        // Calculate OEE components
        $availability = $plannedTime > 0 ? ($runTime / $plannedTime) * 100 : 0;

        // Performance calculation: (Actual Output / (CT Std * Cavity Mold)) / (Run Time / 3600)
        // Convert run time from minutes to seconds (runTime * 60)
        // Performance cannot exceed 100% (machine cannot run faster than standard)
        $performance = 0;
        if ($cycleTimeStd > 0 && $cavityMold > 0 && $runTime > 0) {
            // Calculate theoretical output per hour
            $theoreticalOutputPerHour = (3600 / $cycleTimeStd) * $cavityMold;

            // Calculate actual output per hour from run time
            // Run time is in minutes, so divide by (runTime/60) to get per hour rate
            $actualOutputPerHour = $actualOutput > 0 ? ($actualOutput / ($runTime / 60)) : 0;

            // Performance = (Actual Output Per Hour / Theoretical Output Per Hour) * 100
            $performance = ($theoreticalOutputPerHour > 0) ? ($actualOutputPerHour / $theoreticalOutputPerHour) * 100 : 0;

            // Cap performance at 100% (machine cannot exceed standard speed)
            $performance = min($performance, 100);
        }

        $quality = $actualOutput > 0 ? ($goodOutput / $actualOutput) * 100 : 0;
        $oee = ($availability * $performance * $quality) / 10000;

        return (object) [
            'id' => $planning->id,
            'tanggal' => Carbon::parse($planning->tanggal),
            'mesin' => $planning->mesin,
            'planning' => $planning,
            'actual_output' => $actualOutput,
            'good_output' => $goodOutput,
            'ng_output' => $ngOutput,
            'planned_time' => $plannedTime,
            'run_time' => $runTime,
            'cycle_time_std' => $cycleTimeStd,
            'cavity_mold' => $cavityMold,
            'cycle_time_actual' => $cycleTimeActual,
            'ng_material' => $inputProduksi->ng_material ?? 0,
            'ng_process' => $inputProduksi->ng_process ?? 0,
            'ng_machine' => $inputProduksi->ng_machine ?? 0,
            'ng_operator' => $inputProduksi->ng_operator ?? 0,
            'ng_other' => $inputProduksi->ng_other ?? 0,
            'ng_details' => $inputProduksi->ngPartTypes->map(function ($type) {
                return [
                    'name' => $type->name,
                    'quantity' => $type->pivot->quantity,
                    'category' => $type->pivot->category
                ];
            }),
            'availability_rate' => round($availability, 2),
            'performance_rate' => round($performance, 2),
            'quality_rate' => round($quality, 2),
            'oee_rate' => round($oee, 2),
            'availability_color' => $this->getColorForMetric($availability, [90, 80]),
            'performance_color' => $this->getColorForMetric($performance, [95, 85]),
            'quality_color' => $this->getColorForMetric($quality, [99, 95]),
            'oee_color' => $this->getColorForMetric($oee, [85, 70])
        ];
    }

    /**
     * Calculate run time from ploting data
     */
    private function calculateRunTime($planning)
    {
        $plotings = $planning->plotings;
        $totalRunTime = 0;

        foreach ($plotings as $ploting) {
            if ($ploting->start_time && $ploting->end_time) {
                $start = Carbon::parse($ploting->start_time);
                $end = Carbon::parse($ploting->end_time);
                $totalRunTime += $end->diffInMinutes($start);
            }
        }

        return $totalRunTime > 0 ? $totalRunTime : 480; // Default to 8 hours if no data
    }

    /**
     * Calculate actual cycle time
     */
    private function calculateActualCycleTime($planning, $actualOutput)
    {
        $runTime = $this->calculateRunTime($planning);
        // Cycle time actual = total run time (minutes) / total output
        // Convert to seconds for comparison with standard cycle time
        return $actualOutput > 0 ? ($runTime * 60) / $actualOutput : 60; // Convert to seconds
    }

    /**
     * Get color for metric based on thresholds
     */
    private function getColorForMetric($value, $thresholds)
    {
        if ($value >= $thresholds[0]) return 'success';
        if ($value >= $thresholds[1]) return 'warning';
        return 'danger';
    }

    /**
     * Generate date range based on period
     */
    private function generateDateRange($period, $startDate = null, $endDate = null)
    {
        $dates = [];

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            while ($start->lte($end)) {
                $dates[] = $start->copy();
                $start->addDay();
            }
        } else {
            switch ($period) {
                case '7days':
                    for ($i = 6; $i >= 0; $i--) {
                        $dates[] = now()->subDays($i);
                    }
                    break;
                case '30days':
                    for ($i = 29; $i >= 0; $i--) {
                        $dates[] = now()->subDays($i);
                    }
                    break;
                case 'daily':
                    // Generate hourly data for today - all hours up to current hour
                    $currentHour = now()->hour;
                    for ($i = 0; $i <= $currentHour; $i++) {
                        $dates[] = now()->startOfDay()->addHours($i);
                    }
                    break;
                case 'month':
                default:
                    $start = now()->startOfMonth();
                    $end = now()->endOfMonth();
                    while ($start->lte($end)) {
                        $dates[] = $start->copy();
                        $start->addDay();
                    }
                    break;
            }
        }

        return $dates;
    }

    /**
     * Prepare data for charts from planning data
     */
    private function prepareChartDataFromPlanning($oeeCollection, $period = 'month', $startDate = null, $endDate = null)
    {
        // Group by date or hour based on period
        if ($period === 'daily') {
            $byDate = $oeeCollection->groupBy(function ($item) {
                return $item->tanggal->format('Y-m-d H');
            });
        } else {
            $byDate = $oeeCollection->groupBy(function ($item) {
                return $item->tanggal->format('Y-m-d');
            });
        }

        $dates = [];
        $oee = [];
        $availability = [];
        $performance = [];
        $quality = [];

        // For daily period, just show today's data as single entry
        // Since planning_produksi.tanggal is a DATE field (no time info)
        if ($period === 'daily') {
            // Simply show one data point for today
            $dates[] = 'Today';
            $oee[] = round($oeeCollection->avg('oee_rate'), 1);
            $availability[] = round($oeeCollection->avg('availability_rate'), 1);
            $performance[] = round($oeeCollection->avg('performance_rate'), 1);
            $quality[] = round($oeeCollection->avg('quality_rate'), 1);
        } else {
            // For other periods, generate date range and fill data
            $dateRange = $this->generateDateRange($period, $startDate, $endDate);

            foreach ($dateRange as $date) {
                $dateKey = $date->format('Y-m-d');
                $items = $byDate->get($dateKey, collect());

                // Format date label based on period
                switch ($period) {
                    case '7days':
                        $dates[] = $date->format('d M');
                        break;
                    case '30days':
                        $dates[] = $date->format('d M');
                        break;
                    case 'month':
                    default:
                        $dates[] = $date->format('d M');
                        break;
                }

                // Calculate averages (daily or hourly)
                if ($items->isNotEmpty()) {
                    $oee[] = round($items->avg('oee_rate'), 1);
                    $availability[] = round($items->avg('availability_rate'), 1);
                    $performance[] = round($items->avg('performance_rate'), 1);
                    $quality[] = round($items->avg('quality_rate'), 1);
                } else {
                    // Fill with 0 for periods with no data
                    $oee[] = 0;
                    $availability[] = 0;
                    $performance[] = 0;
                    $quality[] = 0;
                }
            }
        }

        // Machine performance
        $byMachine = $oeeCollection->groupBy(function ($item) {
            return $item->mesin->id ?? 'unknown';
        });
        $machines = [];
        $machineOEE = [];

        foreach ($byMachine as $mesinId => $items) {
            $machines[] = $items->first()->mesin->nama_mesin ?? 'N/A';
            $machineOEE[] = round($items->avg('oee_rate'), 1);
        }

        // NG Categories - using dynamic data from ng_part_types if available
        $ngPartTypes = \App\Models\NgPartType::active()->ordered()->get();
        $ngLabels = [];
        $ngCategories = [];

        if ($ngPartTypes->count() > 0) {
            foreach ($ngPartTypes as $type) {
                $ngLabels[] = $type->name;
                $sum = 0;
                foreach ($oeeCollection as $oee) {
                    if (isset($oee->ng_details)) {
                        $sum += $oee->ng_details->where('name', $type->name)->sum('quantity');
                    }
                }
                $ngCategories[] = $sum;
            }

            // If all sums are 0, maybe they are still using the old columns
            if (array_sum($ngCategories) == 0) {
                $ngLabels = ['Material', 'Process', 'Machine', 'Operator', 'Other'];
                $ngCategories = [
                    $oeeCollection->sum('ng_material'),
                    $oeeCollection->sum('ng_process'),
                    $oeeCollection->sum('ng_machine'),
                    $oeeCollection->sum('ng_operator'),
                    $oeeCollection->sum('ng_other'),
                ];
            }
        } else {
            $ngLabels = ['Material', 'Process', 'Machine', 'Operator', 'Other'];
            $ngCategories = [
                $oeeCollection->sum('ng_material'),
                $oeeCollection->sum('ng_process'),
                $oeeCollection->sum('ng_machine'),
                $oeeCollection->sum('ng_operator'),
                $oeeCollection->sum('ng_other'),
            ];
        }

        // Ensure we have data for all dates in range
        $result = [
            'dates' => $dates,
            'oee' => $oee,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'machines' => $machines,
            'machineOEE' => $machineOEE,
            'ngLabels' => $ngLabels,
            'ngCategories' => $ngCategories,
        ];

        // Debug the result
        Log::info('Chart Data Result', [
            'period' => $period,
            'datesCount' => is_array($dates) ? count($dates) : 0,
            'oeeCount' => is_array($oee) ? count($oee) : 0,
            'availabilityCount' => is_array($availability) ? count($availability) : 0,
            'performanceCount' => is_array($performance) ? count($performance) : 0,
            'qualityCount' => is_array($quality) ? count($quality) : 0,
            'isDailyView' => $period === 'daily',
            'currentHour' => $period === 'daily' ? now()->hour : 'N/A',
            'allDates' => $dates, // Show all dates for daily view
            'sampleData' => [
                'dates' => array_slice((array)$dates, 0, 5),
                'oee' => array_slice((array)$oee, 0, 5),
                'availability' => array_slice((array)$availability, 0, 5),
                'performance' => array_slice((array)$performance, 0, 5),
                'quality' => array_slice((array)$quality, 0, 5)
            ]
        ]);

        return $result;
    }

    /**
     * Prepare data for charts (legacy method)
     */
    private function prepareChartData($oeeCollection)
    {
        // Group by date
        $byDate = $oeeCollection->groupBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

        $dates = [];
        $oee = [];
        $availability = [];
        $performance = [];
        $quality = [];

        foreach ($byDate as $date => $items) {
            $dates[] = Carbon::parse($date)->format('d M');
            $oee[] = round($items->avg('oee_rate'), 1);
            $availability[] = round($items->avg('availability_rate'), 1);
            $performance[] = round($items->avg('performance_rate'), 1);
            $quality[] = round($items->avg('quality_rate'), 1);
        }

        // Machine performance
        $byMachine = $oeeCollection->groupBy('mesin_id');
        $machines = [];
        $machineOEE = [];

        foreach ($byMachine as $mesinId => $items) {
            $machines[] = $items->first()->mesin->nama_mesin ?? 'N/A';
            $machineOEE[] = round($items->avg('oee_rate'), 1);
        }

        // NG Categories
        $ngPartTypes = \App\Models\NgPartType::active()->ordered()->get();
        $ngLabels = [];
        $ngCategories = [];

        if ($ngPartTypes->count() > 0) {
            foreach ($ngPartTypes as $type) {
                $ngLabels[] = $type->name;
                $sum = 0;
                foreach ($oeeCollection as $oee) {
                    if (isset($oee->ng_details)) {
                        $sum += $oee->ng_details->where('name', $type->name)->sum('quantity');
                    }
                }
                $ngCategories[] = $sum;
            }

            if (array_sum($ngCategories) == 0) {
                $ngLabels = ['Material', 'Process', 'Machine', 'Operator', 'Other'];
                $ngCategories = [
                    $oeeCollection->sum('ng_material') ?? 0,
                    $oeeCollection->sum('ng_process') ?? 0,
                    $oeeCollection->sum('ng_machine') ?? 0,
                    $oeeCollection->sum('ng_operator') ?? 0,
                    $oeeCollection->sum('ng_other') ?? 0,
                ];
            }
        } else {
            $ngLabels = ['Material', 'Process', 'Machine', 'Operator', 'Other'];
            $ngCategories = [
                $oeeCollection->sum('ng_material') ?? 0,
                $oeeCollection->sum('ng_process') ?? 0,
                $oeeCollection->sum('ng_machine') ?? 0,
                $oeeCollection->sum('ng_operator') ?? 0,
                $oeeCollection->sum('ng_other') ?? 0,
            ];
        }

        return [
            'dates' => $dates,
            'oee' => $oee,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'machines' => $machines,
            'machineOEE' => $machineOEE,
            'ngLabels' => $ngLabels,
            'ngCategories' => $ngCategories,
        ];
    }

    /**
     * Generate OEE untuk planning tertentu
     * NOTE: Method ini menggunakan calculateOeeForPlanning() yang menghitung real-time
     * Tabel oee_records hanya sebagai backup/archive, tidak digunakan untuk display
     */
    public function generate($planningId)
    {
        try {
            $planning = PlanningProduksi::with(['mesin', 'meja', 'part', 'inputProduksi', 'plotings'])
                ->findOrFail($planningId);

            // Hitung OEE secara real-time (tidak dari tabel oee_records)
            $oee = $this->calculateOeeForPlanning($planning);

            return response()->json([
                'success' => true,
                'message' => 'OEE berhasil di-generate',
                'data' => $oee
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show detail OEE
     */
    public function show($id)
    {
        $planning = PlanningProduksi::with(['mesin', 'meja', 'part', 'moldshop', 'inputProduksi.ngPartTypes', 'plotings'])->findOrFail($id);
        $oee = $this->calculateOeeForPlanning($planning);

        return view('oee.detail', compact('oee'));
    }

    /**
     * Get OEE detail for modal (AJAX)
     */
    public function getDetail($id)
    {
        try {
            $planning = PlanningProduksi::with(['mesin', 'meja', 'part', 'moldshop', 'inputProduksi.ngPartTypes', 'plotings'])->findOrFail($id);
            $oee = $this->calculateOeeForPlanning($planning);

            // Add additional fields for modal display
            $data = (array) $oee;
            $data['mesin_nama'] = $planning->mesin ? $planning->mesin->nama_mesin : null;
            $data['meja_nama'] = $planning->meja ? $planning->meja->nama_meja : null;
            $data['part_no'] = $planning->part ? $planning->part->no_parts : null;
            $data['tanggal_formatted'] = $oee->tanggal ? $oee->tanggal->format('d M Y') : null;
            $data['bagian'] = $planning->bagian ?? 'inject';
            $data['down_time'] = $data['planned_time'] - $data['run_time'];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * API endpoint untuk real-time update
     */
    public function apiCurrent()
    {
        $planningData = PlanningProduksi::with(['mesin', 'meja', 'inputProduksi'])
            ->whereDate('tanggal', today())
            ->whereHas('inputProduksi', function ($q) {
                $q->where('status', 'approved'); // Hanya data yang sudah approved
            })
            ->get();

        $current = $planningData->map(function ($planning) {
            return $this->calculateOeeForPlanning($planning);
        });

        return response()->json([
            'success' => true,
            'data' => $current,
            'avgOEE' => $current->avg('oee_rate')
        ]);
    }

    /**
     * Export OEE dashboard data to Excel using current filters
     */
    public function exportExcel(Request $request)
    {
        // Reuse dashboard query logic to honor filters - HANYA yang sudah approved
        $query = PlanningProduksi::with(['mesin', 'meja', 'part', 'inputProduksi.ngPartTypes', 'plotings'])
            ->whereHas('inputProduksi', function ($q) {
                $q->where('status', 'approved'); // Hanya data yang sudah approved
            }); // Only planning with approved input produksi

        $period = $request->get('period', 'month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Custom date range
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
            // Period-based filtering
            switch ($period) {
                case '7days':
                    $startDate = now()->subDays(6)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case '30days':
                    $startDate = now()->subDays(29)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case 'daily':
                    $startDate = now()->startOfDay()->toDateString();
                    $endDate = now()->endOfDay()->toDateString();
                    $query->whereDate('tanggal', now()->toDateString());
                    break;
                case 'month':
                default:
                    $startDate = now()->startOfMonth()->toDateString();
                    $endDate = now()->endOfMonth()->toDateString();
                    $query->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year);
                    break;
            }
        }

        // Filter by bagian
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        // Filter by mesin (only for inject)
        if ($request->filled('mesin_id')) {
            $query->where('mesin_id', $request->mesin_id);
        }

        // Filter by meja (only for assy)
        if ($request->filled('meja_id')) {
            $query->where('meja_id', $request->meja_id);
        }

        // Get ALL planning data (without pagination) for Excel export
        $planningData = $query->orderBy('tanggal', 'desc')->get();

        // Debug: Log the count to verify data
        Log::info('OEE Excel Export', [
            'planning_count' => $planningData->count(),
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mesin_id' => $request->mesin_id ?? null,
        ]);

        $oeeData = $planningData->map(function ($planning) {
            return $this->calculateOeeForPlanning($planning);
        });

        $fileName = 'oee_dashboard_' . ($period ?: 'month') . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new OeeExport($oeeData, [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'mesin_id' => $request->mesin_id ?? null,
        ]), $fileName);
    }

    /**
     * Export OEE dashboard data to PDF using current filters
     */
    public function exportPdf(Request $request)
    {
        // Reuse dashboard query logic to honor filters - HANYA yang sudah approved
        $query = PlanningProduksi::with(['mesin', 'meja', 'part', 'inputProduksi.ngPartTypes', 'plotings'])
            ->whereHas('inputProduksi', function ($q) {
                $q->where('status', 'approved'); // Hanya data yang sudah approved
            }); // Only planning with approved input produksi

        $period = $request->get('period', 'month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Custom date range
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
            // Period-based filtering
            switch ($period) {
                case '7days':
                    $startDate = now()->subDays(6)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case '30days':
                    $startDate = now()->subDays(29)->toDateString();
                    $endDate = now()->toDateString();
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                    break;
                case 'daily':
                    $startDate = now()->startOfDay()->toDateString();
                    $endDate = now()->endOfDay()->toDateString();
                    $query->whereDate('tanggal', now()->toDateString());
                    break;
                case 'month':
                default:
                    $startDate = now()->startOfMonth()->toDateString();
                    $endDate = now()->endOfMonth()->toDateString();
                    $query->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year);
                    break;
            }
        }

        // Filter by bagian
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        // Filter by mesin (only for inject)
        if ($request->filled('mesin_id')) {
            $query->where('mesin_id', $request->mesin_id);
        }

        // Filter by meja (only for assy)
        if ($request->filled('meja_id')) {
            $query->where('meja_id', $request->meja_id);
        }

        // Get ALL planning data (without pagination) for PDF export
        $planningData = $query->orderBy('tanggal', 'desc')->get();

        // Debug: Log the count to verify data
        Log::info('OEE PDF Export', [
            'planning_count' => $planningData->count(),
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mesin_id' => $request->mesin_id ?? null,
        ]);

        $oeeData = $planningData->map(function ($planning) {
            return $this->calculateOeeForPlanning($planning);
        });

        // Calculate averages from ALL data
        $avgOEE = $oeeData->avg('oee_rate') ?? 0;
        $avgAvailability = $oeeData->avg('availability_rate') ?? 0;
        $avgPerformance = $oeeData->avg('performance_rate') ?? 0;
        $avgQuality = $oeeData->avg('quality_rate') ?? 0;

        // Prepare chart data
        $chartData = $this->prepareChartDataFromPlanning($oeeData, $period, $startDate, $endDate);

        // Build chart image URLs via QuickChart for PDF rendering
        $trendChartUrl = $this->buildQuickChartUrl([
            'type' => 'line',
            'data' => [
                'labels' => array_values((array)$chartData['dates']),
                'datasets' => [
                    ['label' => 'OEE', 'data' => array_values((array)$chartData['oee']), 'borderColor' => '#4e73df', 'fill' => false],
                    ['label' => 'Availability', 'data' => array_values((array)$chartData['availability']), 'borderColor' => '#1cc88a', 'fill' => false],
                    ['label' => 'Performance', 'data' => array_values((array)$chartData['performance']), 'borderColor' => '#f6c23e', 'fill' => false],
                    ['label' => 'Quality', 'data' => array_values((array)$chartData['quality']), 'borderColor' => '#36b9cc', 'fill' => false],
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales' => ['y' => ['suggestedMin' => 0, 'suggestedMax' => 100]]
            ],
        ], 1000, 300);

        $machineChartUrl = $this->buildQuickChartUrl([
            'type' => 'bar',
            'data' => [
                'labels' => array_values((array)$chartData['machines']),
                'datasets' => [
                    ['label' => 'OEE', 'data' => array_values((array)$chartData['machineOEE']), 'backgroundColor' => '#4e73df'],
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['suggestedMin' => 0, 'suggestedMax' => 100]]
            ],
        ], 600, 300);

        $ngChartUrl = $this->buildQuickChartUrl([
            'type' => 'doughnut',
            'data' => [
                'labels' => array_values((array)$chartData['ngLabels']),
                'datasets' => [[
                    'data' => array_values((array)$chartData['ngCategories']),
                    'backgroundColor' => ['#e74a3b', '#f6c23e', '#858796', '#1cc88a', '#36b9cc', '#4e73df', '#6610f2', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#17a2b8', '#520dc2', '#b027b0', '#d63384', '#dc3545', '#ffc107', '#28a745', '#007bff', '#6c757d', '#343a40']
                ]],
            ],
            'options' => ['plugins' => ['legend' => ['display' => true]]],
        ], 400, 300);

        $html = view('oee.pdf', [
            'oeeData' => $oeeData,
            'avgOEE' => $avgOEE,
            'avgAvailability' => $avgAvailability,
            'avgPerformance' => $avgPerformance,
            'avgQuality' => $avgQuality,
            'chartData' => $chartData,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'trendChartUrl' => $trendChartUrl,
            'machineChartUrl' => $machineChartUrl,
            'ngChartUrl' => $ngChartUrl,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'oee_dashboard_' . ($period ?: 'month') . '_' . date('Y-m-d_H-i-s') . '.pdf';
        return response()->make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    private function buildQuickChartUrl(array $config, int $width = 800, int $height = 400): string
    {
        $payload = [
            'c' => $config,
            'w' => $width,
            'h' => $height,
            'format' => 'png',
            'version' => '3.9.1',
            'backgroundColor' => 'white',
        ];
        $base = 'https://quickchart.io/chart';
        // Use query string encoding; ensure JSON is compact
        $query = http_build_query(['c' => json_encode($config), 'w' => $width, 'h' => $height, 'format' => 'png', 'version' => '3.9.1', 'backgroundColor' => 'white']);
        return $base . '?' . $query;
    }
}
