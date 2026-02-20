<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\User;
use App\Models\Part;
use App\Models\Checksheet;
use App\Models\InputProduksi;
use App\Models\DowntimeEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DashboardInjectController extends Controller
{
    /**
     * Display the dashboard for inject department.
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user is authorized for inject department (only if logged in)
        if ($user && $user->role === 'operator' && $user->bagian !== 'inject') {
            return redirect()->route('menu')->with('error', 'Anda tidak memiliki akses ke Dashboard Inject.');
        }

        // Get users for the form (filter by bagian inject)
        if ($user && $user->role === 'operator') {
            // Operator hanya bisa pilih dirinya sendiri
            $users = User::where('id', $user->id)->get();
        } else {
            // super_admin, kabag, manager_plant, or GUEST -> semua user (for dropdowns etc)
            $users = User::all();
        }

        // Data mesin lengkap untuk perhitungan statistik dan OEE
        $allMachines = Mesin::all();

        // Daftar mesin untuk tampilan kartu (tampilkan semua 35 mesin)
        // Urutkan berdasarkan nomor mesin secara numerik (Mesin 1, Mesin 2, ..., Mesin 35)
        $machines = Mesin::orderByRaw('CAST(SUBSTRING_INDEX(nama_mesin, " ", -1) AS UNSIGNED)')->get();

        // Count total machines (pakai koleksi lengkap)
        $totalMesins = $allMachines->count();
        $countAktif = $allMachines->where('status', 'running')->count();
        $countStop = $allMachines->where('status', 'stop')->count();
        $countMaintenance = $allMachines->where('status', 'maintenance')->count();
        $countTidakAktif = $allMachines->where('status', 'offline')->count();

        $countSetup = $allMachines->where('status', 'stop')->where('stop_reason', 'setup')->count();
        $countSetdown = $allMachines->where('status', 'stop')->where('stop_reason', 'setdown')->count();
        $countQcCek = $allMachines->where('status', 'stop')->where('stop_reason', 'qc_cek')->count();
        $countMaterialHabis = $allMachines->where('status', 'stop')->where('stop_reason', 'material_habis')->count();
        $countPackagingHabis = $allMachines->where('status', 'stop')->where('stop_reason', 'packaging_habis')->count();

        // Get all parts for the form
        $parts = Part::all();

        // Get all grups for the form (filter by bagian inject via users)
        $grups = \App\Models\Grup::whereHas('users', function ($query) {
            $query->where('bagian', 'inject');
        })
            ->orderBy('nama_grup')
            ->get();

        // Get today's date
        $today = \Carbon\Carbon::today();

        // Get current hour to determine current shift
        $currentHour = (int) \Carbon\Carbon::now()->format('H');
        $currentShift = null;
        if ($currentHour >= 7 && $currentHour < 15) {
            $currentShift = 'pagi';
        } elseif ($currentHour >= 15 && $currentHour < 23) {
            $currentShift = 'siang';
        } else {
            $currentShift = 'malam';
        }

        // Initialize machine data arrays
        $machineProgress = [];
        $machineProductionData = [];
        $checksheetDetails = [];

        // Get production and progress data from helper method
        $prodResult = $this->calculateProductionData();
        $machineProductionData = $prodResult['machines'];
        $aggregates = $prodResult['aggregates'];


        // Finalize metadata for the view
        foreach ($machines as $mesin) {
            $machineProgress[$mesin->id] = $machineProductionData[$mesin->id]['progress_percentage'] ?? 0;

            $checksheetDetails[$mesin->id] = [
                'maintenance' => $mesin->maintenance_count > 0,
                'offline' => $mesin->offline_count > 0
            ];
        }

        return view('dashboardinject.index', compact(
            'machines',
            'totalMesins',
            'countAktif',
            'countMaintenance',
            'countTidakAktif',
            'countSetup',
            'countSetdown',
            'countQcCek',
            'countMaterialHabis',
            'countPackagingHabis',
            'countStop',
            'users',
            'parts',
            'grups',
            'machineProgress',
            'machineProductionData',
            'checksheetDetails',
            'aggregates'
        ));
    }

    /**
     * Display the history of machines.
     */
    public function history(Request $request)
    {
        $startDate = $request->input('start_date', $request->input('date', date('Y-m-d')));
        $endDate = $request->input('end_date', $request->input('date', date('Y-m-d')));

        \Illuminate\Support\Facades\Log::info("History Filter Called: Start=$startDate End=$endDate MesinID=" . ($request->mesin_id ?? 'All'));

        $shift = $request->input('shift');
        $now = \Carbon\Carbon::now();

        $isToday = $endDate === $now->format('Y-m-d');
        $date = $startDate; // For backward compatibility in view if needed

        // Fetch events: 
        // 1. That are within the selected date range
        // 2. That are still open and started before/during the range
        // 3. That were closed/updated during the range (even if started before)
        $events = \App\Models\DowntimeEvent::with(['mesin', 'user'])
            ->where(function ($q) use ($startDate, $endDate) {
                // Event occurs within the range
                $q->whereBetween('tanggal', [$startDate, $endDate])
                    // OR event is still open and started before/during the end of range
                    ->orWhere(function ($q2) use ($endDate) {
                        $q2->whereNull('end_time')
                            ->whereDate('tanggal', '<=', $endDate);
                    })
                    // OR period starting before but ended during the range
                    // Since we don't have end_date, we'll look at updated_at as a proxy 
                    // to catch events closed/modified during the selected view days.
                    ->orWhere(function ($q3) use ($startDate, $endDate) {
                        $q3->where('tanggal', '<', $startDate)
                            ->whereDate('updated_at', '>=', $startDate);
                    });
            })
            ->when($request->mesin_id, function ($q) use ($request) {
                $q->where('mesin_id', $request->mesin_id);
            })
            ->when($shift, function ($q) use ($shift) {
                $q->where('shift', $shift);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        // Get all machines
        $allMesins = \App\Models\Mesin::orderByRaw('CAST(SUBSTRING_INDEX(nama_mesin, " ", -1) AS UNSIGNED)')->get();

        // Inject virtual ongoing events for machines that are currently active but have no open event in DB
        if ($isToday) {
            foreach ($allMesins as $mesin) {
                $mStatus = strtolower(trim($mesin->status));
                if (!$mStatus) continue;

                // Check if this machine has an open event that fits the SAME CATEGORY
                // Categories: aktif (running/aktif), maintenance (maintenance/breakdown), offline (offline/tidak aktif), standby (the rest)
                $category = ($mStatus === 'running' || $mStatus === 'aktif') ? 'aktif' : (($mStatus === 'maintenance' || $mStatus === 'breakdown') ? 'maintenance' : (($mStatus === 'offline' || $mStatus === 'tidak aktif') ? 'offline' : 'standby'));

                $hasOpenMatch = $events->where('mesin_id', $mesin->id)
                    ->whereNull('end_time')
                    ->filter(function ($e) use ($category) {
                        $eType = strtolower($e->downtime_type);
                        $eCat = ($eType === 'running' || $eType === 'aktif') ? 'aktif' : (($eType === 'maintenance' || $eType === 'breakdown') ? 'maintenance' : (($eType === 'offline' || $eType === 'tidak aktif') ? 'offline' : 'standby'));
                        return $eCat === $category;
                    })
                    ->first();

                if (!$hasOpenMatch) {
                    $statusSince = $mesin->status_since ? \Carbon\Carbon::parse($mesin->status_since) : \Carbon\Carbon::parse($startDate)->startOfDay();

                    $virtualEvent = new \App\Models\DowntimeEvent();
                    $virtualEvent->mesin_id = $mesin->id;
                    $virtualEvent->downtime_type = $mStatus;
                    $virtualEvent->tanggal = $statusSince;
                    $virtualEvent->start_time = $statusSince->format('H:i:s');
                    $virtualEvent->end_time = null;
                    $virtualEvent->user_id = null;
                    $virtualEvent->description = 'Durasi berjalan (otomatis)';

                    $virtualEvent->setRelation('mesin', $mesin);
                    $events->push($virtualEvent);
                }
            }
        }

        // Filter Logic for log table: Show all activity by default
        $filteredEvents = $events;

        // Calculate summaries with ongoing events support
        $rangeStart = \Carbon\Carbon::parse($startDate)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
        $rangeEndFinal = $isToday ? $now : $rangeEnd;

        // Helper to calculate duration within range for an event
        $getEventDurationInRange = function ($event) use ($rangeStart, $rangeEndFinal) {
            $startDateStr = is_string($event->tanggal) ? $event->tanggal : ($event->tanggal instanceof \Carbon\Carbon ? $event->tanggal->format('Y-m-d') : $event->tanggal);
            $eventStart = \Carbon\Carbon::parse($startDateStr . ' ' . $event->start_time);

            if ($event->end_time) {
                // Use updated_at as a proxy for the end date since DB lacks end_date
                $endDateStr = $event->updated_at->format('Y-m-d');
                if ($endDateStr < $startDateStr) $endDateStr = $startDateStr;
                $eventEnd = \Carbon\Carbon::parse($endDateStr . ' ' . $event->end_time);

                // Handle cross-midnight if still ends "before" start on the same proxy day
                if ($eventEnd->lt($eventStart)) {
                    $eventEnd->addDay();
                }
            } else {
                $eventEnd = $rangeEndFinal->copy();
            }

            $actualStart = $eventStart->max($rangeStart);
            $actualEnd = $eventEnd->min($rangeEndFinal);

            return ($actualStart->lt($actualEnd)) ? $actualStart->diffInSeconds($actualEnd) : 0;
        };

        $calculateTotal = function ($type) use ($events, $getEventDurationInRange) {
            $types = ($type === 'offline') ? ['offline', 'tidak aktif'] : (array)$type;
            return $events->whereIn('downtime_type', $types)->sum($getEventDurationInRange);
        };

        $calculateBase = function ($type) use ($events, $getEventDurationInRange) {
            $types = ($type === 'offline') ? ['offline', 'tidak aktif'] : (array)$type;
            return $events->whereIn('downtime_type', $types)->whereNotNull('end_time')->sum($getEventDurationInRange);
        };

        $totalMaintenanceSeconds = $calculateTotal('maintenance');
        $totalOfflineSeconds = $calculateTotal('offline');

        $excludeTypes = ['aktif', 'running', 'maintenance', 'offline', 'tidak aktif'];
        $totalStandbySeconds = $events->whereNotIn('downtime_type', $excludeTypes)->sum($getEventDurationInRange);
        $totalRuntimeSeconds = $calculateTotal(['aktif', 'running']);

        // Base values (completed only) for JS counters
        $baseMaintenanceSeconds = $calculateBase('maintenance');
        $baseOfflineSeconds = $calculateBase('offline');
        $baseStandbySeconds = $events->whereNotIn('downtime_type', $excludeTypes)->whereNotNull('end_time')->sum($getEventDurationInRange);
        $baseRuntimeSeconds = $calculateBase(['aktif', 'running']);


        // Calculate per-machine totals
        $machineTotals = $allMesins->map(function ($mesin) use ($events, $getEventDurationInRange) {
            $machineEvents = $events->where('mesin_id', $mesin->id);
            $completedEvents = $machineEvents->whereNotNull('end_time');

            $getSum = function ($queryset, $types) use ($getEventDurationInRange) {
                return $queryset->whereIn('downtime_type', (array)$types)->sum($getEventDurationInRange);
            };

            $getSumStandby = function ($queryset) use ($getEventDurationInRange) {
                $exclude = ['aktif', 'running', 'maintenance', 'offline', 'tidak aktif'];
                return $queryset->whereNotIn('downtime_type', $exclude)->sum($getEventDurationInRange);
            };

            return [
                'id' => $mesin->id,
                'nama_mesin' => $mesin->nama_mesin,
                'status' => $mesin->status,
                'stop_reason' => $mesin->stop_reason,
                // Full totals
                'runtime' => $getSum($machineEvents, ['aktif', 'running']),
                'maintenance' => $getSum($machineEvents, 'maintenance'),
                'offline' => $getSum($machineEvents, ['offline', 'tidak aktif']),
                'standby' => $getSumStandby($machineEvents),
                // Base totals (completed only) for JS
                'base_runtime' => $getSum($completedEvents, ['aktif', 'running']),
                'base_maintenance' => $getSum($completedEvents, 'maintenance'),
                'base_offline' => $getSum($completedEvents, ['offline', 'tidak aktif']),
                'base_standby' => $getSumStandby($completedEvents),
            ];
        });

        // Calculate reason-specific totals
        $reasonTotals = $events->groupBy('downtime_type')->map(function ($group) use ($getEventDurationInRange) {
            return $group->sum($getEventDurationInRange);
        })->sortDesc();
        // Prepare events for JS modal consumption (including running events)
        $historyLog = $events->map(function ($e) use ($getEventDurationInRange, $rangeStart, $rangeEndFinal, $isToday, $startDate, $endDate) {
            $startDateStr = is_string($e->tanggal) ? $e->tanggal : $e->tanggal->format('Y-m-d');
            $eventStart = \Carbon\Carbon::parse($startDateStr . ' ' . $e->start_time);

            if ($e->end_time) {
                $endDateStr = $e->updated_at->format('Y-m-d');
                if ($endDateStr < $startDateStr) $endDateStr = $startDateStr;
                $eventEnd = \Carbon\Carbon::parse($endDateStr . ' ' . $e->end_time);
                if ($eventEnd->lt($eventStart)) $eventEnd->addDay();
            } else {
                $eventEnd = $rangeEndFinal->copy();
            }

            $clampedStart = $eventStart->max($rangeStart);
            $clampedEnd = $eventEnd->min($rangeEndFinal);

            // Determine format based on date range (is range view?) using robust comparison
            $isRangeView = \Carbon\Carbon::parse($startDate)->startOfDay()->ne(\Carbon\Carbon::parse($endDate)->startOfDay());
            $format = $isRangeView ? 'd M H:i' : 'H:i:s';

            $startDisplay = $clampedStart->format($format);
            if ($eventStart->lt($rangeStart)) $startDisplay .= ' (Lant.)';

            $endDisplay = '-';
            if ($e->end_time) {
                $endDisplay = $clampedEnd->format($format);
                if ($eventEnd->gt($rangeEndFinal)) $endDisplay .= ' (Berl.)';
            } else {
                // If event is open but we are looking at past history, clamp display to end of range
                if (!$isToday) {
                    $endDisplay = $rangeEndFinal->format($format);
                }
            }

            return [
                'mesin_id' => $e->mesin_id,
                'mesin_nama' => $e->mesin->nama_mesin ?? '-',
                'type' => $e->downtime_type,
                'user' => $e->user->name ?? ($e->user->nama ?? 'System / Auto'),
                'role' => strtolower($e->user->role ?? 'Monitoring'),
                'start' => $startDisplay,
                'end' => $endDisplay,
                'start_raw' => $clampedStart->format('Y-m-d\TH:i:s'),
                // Only mark as ongoing if it's actually ongoing AND we are viewing today
                // If viewing yesterday, an ongoing event should be treated as capped at midnight
                'is_ongoing' => is_null($e->end_time) && $isToday,
                'duration_seconds' => $getEventDurationInRange($e),
                'keterangan' => $e->description ?? $e->keterangan ?? '-'
            ];
        });

        return view('dashboardinject.history', compact(
            'events',
            'historyLog',
            'startDate',
            'endDate',
            'date',
            'totalMaintenanceSeconds',
            'totalOfflineSeconds',
            'totalStandbySeconds',
            'totalRuntimeSeconds',
            'baseMaintenanceSeconds',
            'baseOfflineSeconds',
            'baseStandbySeconds',
            'baseRuntimeSeconds',
            'machineTotals',
            'reasonTotals'
        ));
    }

    /**
     * Display the print view for history of machines.
     */
    public function historyPrint(Request $request)
    {
        $startDate = $request->input('start_date', $request->input('date', date('Y-m-d')));
        $endDate = $request->input('end_date', $request->input('date', date('Y-m-d')));
        $shift = $request->input('shift');
        $now = \Carbon\Carbon::now();
        $isToday = $endDate === $now->format('Y-m-d');
        $date = $startDate;

        $events = \App\Models\DowntimeEvent::with(['mesin', 'user'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($endDate) {
                        $q2->whereNull('end_time')->whereDate('tanggal', '<=', $endDate);
                    });
            })
            ->when($shift, function ($q) use ($shift) {
                $q->where('shift', $shift);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        // Inject virtual events for accurate current state
        if ($isToday) {
            foreach (\App\Models\Mesin::all() as $mesin) {
                if (!$events->where('mesin_id', $mesin->id)->whereNull('end_time')->first() && $mesin->status_since) {
                    $virtual = new \App\Models\DowntimeEvent([
                        'mesin_id' => $mesin->id,
                        'downtime_type' => $mesin->status,
                        'tanggal' => $mesin->status_since->format('Y-m-d'),
                        'start_time' => $mesin->status_since->format('H:i:s'),
                        'end_time' => null,
                        'user_id' => null,
                        'description' => 'Durasi berjalan (aktual)'
                    ]);
                    $virtual->setRelation('mesin', $mesin);
                    $events->push($virtual);
                }
            }
        }

        $rangeStart = \Carbon\Carbon::parse($startDate)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
        $rangeEndFinal = $isToday ? $now : $rangeEnd;

        $getEventDurationInRange = function ($event) use ($rangeStart, $rangeEndFinal) {
            $startDateStr = is_string($event->tanggal) ? $event->tanggal : $event->tanggal->format('Y-m-d');
            $eventStart = \Carbon\Carbon::parse($startDateStr . ' ' . $event->start_time);

            if ($event->end_time) {
                $endDateStr = $event->updated_at->format('Y-m-d');
                if ($endDateStr < $startDateStr) $endDateStr = $startDateStr;
                $eventEnd = \Carbon\Carbon::parse($endDateStr . ' ' . $event->end_time);
                if ($eventEnd->lt($eventStart)) $eventEnd->addDay();
            } else {
                $eventEnd = $rangeEndFinal->copy();
            }

            $actualStart = $eventStart->max($rangeStart);
            $actualEnd = $eventEnd->min($rangeEndFinal);
            return ($actualStart->lt($actualEnd)) ? $actualStart->diffInSeconds($actualEnd) : 0;
        };

        $calculateTotal = function ($type) use ($events, $getEventDurationInRange) {
            return $events->where('downtime_type', $type)->sum($getEventDurationInRange);
        };

        $totalMaintenanceSeconds = $calculateTotal('maintenance');
        $totalOfflineSeconds = $calculateTotal('offline') + $calculateTotal('tidak aktif');
        $excludeTypes = ['aktif', 'running', 'maintenance', 'offline', 'tidak aktif'];
        $totalStandbySeconds = $events->whereNotIn('downtime_type', $excludeTypes)->sum($getEventDurationInRange);
        $totalRuntimeSeconds = $calculateTotal('aktif') + $calculateTotal('running');

        $allMesins = \App\Models\Mesin::orderByRaw('CAST(SUBSTRING_INDEX(nama_mesin, " ", -1) AS UNSIGNED)')->get();

        $machineTotals = $allMesins->map(function ($mesin) use ($events, $getEventDurationInRange) {
            $machineEvents = $events->where('mesin_id', $mesin->id);

            $getSum = function ($queryset, $types) use ($getEventDurationInRange) {
                return $queryset->whereIn('downtime_type', (array)$types)->sum($getEventDurationInRange);
            };

            $getSumStandby = function ($queryset) use ($getEventDurationInRange) {
                $exclude = ['aktif', 'running', 'maintenance', 'offline', 'tidak aktif'];
                return $queryset->whereNotIn('downtime_type', $exclude)->sum($getEventDurationInRange);
            };

            return [
                'id' => $mesin->id,
                'nama_mesin' => $mesin->nama_mesin,
                'status' => $mesin->status,
                'stop_reason' => $mesin->stop_reason,
                'runtime' => $getSum($machineEvents, ['aktif', 'running']),
                'maintenance' => $getSum($machineEvents, 'maintenance'),
                'offline' => $getSum($machineEvents, ['offline', 'tidak aktif']),
                'standby' => $getSumStandby($machineEvents),
            ];
        });

        $reasonTotals = $events->groupBy('downtime_type')->map(function ($group) use ($getEventDurationInRange) {
            return $group->sum($getEventDurationInRange);
        })->sortDesc();

        return view('dashboardinject.history-print', compact(
            'events',
            'startDate',
            'endDate',
            'date',
            'now',
            'isToday',
            'totalMaintenanceSeconds',
            'totalOfflineSeconds',
            'totalStandbySeconds',
            'totalRuntimeSeconds',
            'machineTotals',
            'reasonTotals'
        ));
    }


    /**
     * Store checksheet data from dashboard
     */
    public function storeChecksheet(Request $request)
    {
        Log::info('=== STORE CHECKSHEET METHOD CALLED ===');

        // Log user info for debugging
        $user = Auth::user();
        Log::info('User attempting to store checksheet', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->nama,
            'user_bagian' => $user->bagian ?? 'N/A'
        ]);

        // Log the incoming request for debugging
        Log::info('Checksheet store request received', [
            'data' => $request->all(),
            'files' => $request->allFiles(),
            'has_files' => $request->hasFile('foto_apd') || $request->hasFile('foto_instruksi_kerja') || $request->hasFile('foto_quality') || $request->hasFile('foto_limit_sampel'),
            'file_count' => count($request->allFiles()),
            'content_type' => $request->header('Content-Type')
        ]);

        // Authorization check for staff
        if (Auth::user()->role === 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Staff Produksi tidak memiliki hak akses untuk mengisi checksheet.'
            ], 403);
        }

        // Validate the request
        $request->validate([
            'mesin_id' => 'required|exists:mesin,id',
            'user_id' => 'required|exists:users,id',
            'part_id' => 'required|exists:parts,id',
            'grup_id' => 'required|exists:grups,id',
            'apd_topi' => 'required|in:ya,tidak',
            'apd_sepatu' => 'required|in:ya,tidak',
            'quality_qpoint' => 'required|in:ya,tidak',
            'instruksi_kerja' => 'required|in:ya,tidak',
            'limit_sampel' => 'required|in:ya,tidak',
            'amptm_standar' => 'required|in:ya,tidak',
            'foto_apd' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_instruksi_kerja' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_quality' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_limit_sampel' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'foto_amptm' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $checksheet = Checksheet::create([
                'mesin_id' => $request->mesin_id,
                'user_id' => $request->user_id,
                'grup_id' => $request->grup_id,
                'part_id' => $request->part_id,
                'approval_status' => 'pending',
                'apd_topi' => $request->apd_topi,
                'apd_sepatu' => $request->apd_sepatu,
                'quality_qpoint' => $request->quality_qpoint,
                'instruksi_kerja' => $request->instruksi_kerja,
                'limit_sampel' => $request->limit_sampel,
                'amptm_standar' => $request->amptm_standar,
            ]);

            // Handle photo uploads
            $photoTypes = ['foto_apd', 'foto_instruksi_kerja', 'foto_quality', 'foto_limit_sampel', 'foto_amptm'];
            foreach ($photoTypes as $type) {
                if ($request->hasFile($type)) {
                    $path = $request->file($type)->store('checksheets', 'public');
                    $checksheet->$type = $path;
                }
            }
            $checksheet->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Checksheet berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing checksheet: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get realtime production data for dashboard
     */
    public function getRealtimeProductionData()
    {
        try {
            $result = $this->calculateProductionData();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching realtime production data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Internal helper to calculate production data for both index and realtime API
     */
    private function calculateProductionData()
    {
        // Get today's date
        $today = \Carbon\Carbon::today();

        // Get current hour to determine current shift
        $currentHour = (int) \Carbon\Carbon::now()->format('H');
        $currentShift = null;
        if ($currentHour >= 7 && $currentHour < 15) {
            $currentShift = 'pagi';
        } elseif ($currentHour >= 15 && $currentHour < 23) {
            $currentShift = 'siang';
        } else {
            $currentShift = 'malam';
        }

        // Get all machines
        $machines = Mesin::all();

        // --- DURATION CALCULATION LOGIC (from history method) ---
        $now = \Carbon\Carbon::now();
        $rangeStart = \Carbon\Carbon::today()->startOfDay();
        $rangeEndFinal = $now;

        // Fetch events for today or still open
        $allEvents = \App\Models\DowntimeEvent::where(function ($q) use ($today) {
            $q->whereDate('tanggal', $today)
                ->orWhere(function ($q2) use ($today) {
                    $q2->whereNull('end_time')
                        ->whereDate('tanggal', '<=', $today);
                });
        })->orderBy('start_time', 'asc')->get()->groupBy('mesin_id');

        $getEventDurationInRange = function ($event) use ($rangeStart, $rangeEndFinal) {
            $startDateStr = is_string($event->tanggal) ? $event->tanggal : ($event->tanggal instanceof \Carbon\Carbon ? $event->tanggal->format('Y-m-d') : $event->tanggal);
            $eventStart = \Carbon\Carbon::parse($startDateStr . ' ' . $event->start_time);

            if ($event->end_time) {
                $endDateStr = $event->updated_at->format('Y-m-d');
                if ($endDateStr < $startDateStr) $endDateStr = $startDateStr;
                $eventEnd = \Carbon\Carbon::parse($endDateStr . ' ' . $event->end_time);
                if ($eventEnd->lt($eventStart)) $eventEnd->addDay();
            } else {
                $eventEnd = $rangeEndFinal->copy();
            }

            $actualStart = $eventStart->max($rangeStart);
            $actualEnd = $eventEnd->min($rangeEndFinal);
            return ($actualStart->lt($actualEnd)) ? $actualStart->diffInSeconds($actualEnd) : 0;
        };
        // -------------------------------------------------------

        // Helper to format seconds to HH:mm:ss
        $formatSec = function ($seconds) {
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        };

        // Initialize aggregate durations
        $aggRunningSec = 0;
        $aggStandbySec = 0;
        $aggDowntimeSec = 0;
        $aggOfflineSec = 0;

        $data = [];

        foreach ($machines as $mesin) {
            // Calculate Total Durations per machine for aggregation
            $machineEvents = $allEvents->get($mesin->id, collect());

            $mStatus = strtolower(trim($mesin->status));

            // Helper to get total seconds for a category for this machine
            $getMachineDurations = function ($types) use ($machineEvents, $getEventDurationInRange) {
                return $machineEvents->whereIn('downtime_type', (array)$types)->sum($getEventDurationInRange);
            };

            $getMachineStandbyDurations = function () use ($machineEvents, $getEventDurationInRange) {
                $exclude = ['aktif', 'running', 'maintenance', 'offline', 'tidak aktif', 'breakdown']; // Added breakdown to exclude
                return $machineEvents->whereNotIn('downtime_type', $exclude)->sum($getEventDurationInRange);
            };

            $runtimeSec = $getMachineDurations(['running', 'aktif']);
            $downtimeSec = $getMachineDurations(['maintenance', 'breakdown']);
            $offlineSec = $getMachineDurations(['offline', 'tidak aktif']);
            $standbySec = $getMachineStandbyDurations();

            // Handle ongoing statuses
            // Check if there's an open event for *any* type. If not, the current machine status is ongoing.
            $hasOpenEvent = $machineEvents->whereNull('end_time')->first();
            if (!$hasOpenEvent) {
                $statusSince = $mesin->status_since ? \Carbon\Carbon::parse($mesin->status_since) : $rangeStart;
                $effectiveStart = $statusSince->max($rangeStart);
                $ongoingDuration = $effectiveStart->diffInSeconds($rangeEndFinal);

                if ($mStatus === 'running' || $mStatus === 'aktif') {
                    $runtimeSec += $ongoingDuration;
                } elseif ($mStatus === 'maintenance' || $mStatus === 'breakdown') {
                    $downtimeSec += $ongoingDuration;
                } elseif ($mStatus === 'offline' || $mStatus === 'tidak aktif') {
                    $offlineSec += $ongoingDuration;
                } else { // Any other status is considered standby
                    $standbySec += $ongoingDuration;
                }
            }

            // Accumulate to aggregates
            $aggRunningSec += $runtimeSec;
            $aggStandbySec += $standbySec;
            $aggDowntimeSec += $downtimeSec;
            $aggOfflineSec += $offlineSec;

            // Format per-machine runtime for the simple card
            $hours = floor($runtimeSec / 3600);
            $minutes = floor(($runtimeSec % 3600) / 60);
            $totalJamDisplay = sprintf('%02d:%02d', $hours, $minutes);

            // Get today's planning for this machine
            // Try to be more inclusive with status for the dashboard
            $plannings = \App\Models\PlanningProduksi::with(['inputProduksi', 'part', 'plotings.operator', 'moldshop'])
                ->where('mesin_id', $mesin->id)
                ->whereDate('tanggal', $today)
                // ->where('status_approval', 'approved') // Temporarily commented to check if this is the issue
                ->get();

            // Initialize data
            $targetHariIni = 0;
            $targetShiftPagi = 0;
            $targetShiftSiang = 0;
            $targetShiftMalam = 0;
            $actualHariIni = 0;
            $actualShiftPagi = 0;
            $actualShiftSiang = 0;
            $actualShiftMalam = 0;
            $keterangan = '';
            $partName = '';
            $customer = '-';
            $operatorName = '-';

            foreach ($plannings as $planning) {
                // Sum targets
                $targetShiftPagi += $planning->target_shift_pagi ?? 0;
                $targetShiftSiang += $planning->target_shift_siang ?? 0;
                $targetShiftMalam += $planning->target_shift_malam ?? 0;
                $targetHariIni += $planning->qty_planning_produksi ?? 0;

                // Get part name
                if ($planning->part) {
                    $partName = $planning->part->nama_part;
                }

                // Get customer
                if ($planning->moldshop) {
                    $customer = $planning->moldshop->customer ?? '-';
                }

                // Get actual production from input_produksi
                if ($planning->inputProduksi) {
                    $ok = $planning->inputProduksi->ok_count ?? 0;
                    $ng = $planning->inputProduksi->ng_count ?? 0;
                    $actualHariIni += ($ok + $ng);
                }
            }

            // Get Operator Name logic
            $foundOperator = false;
            foreach ($plannings as $p) {
                foreach ($p->plotings as $ploting) {
                    if ($ploting->status === 'on_progress' && $ploting->operator && isset($ploting->operator->nama)) {
                        $operatorName = $ploting->operator->nama;
                        $foundOperator = true;
                        break 2;
                    }
                }
            }
            if (!$foundOperator) {
                foreach ($plannings as $p) {
                    if ($p->plotings->isNotEmpty()) {
                        foreach ($p->plotings as $ploting) {
                            if ($ploting->operator && isset($ploting->operator->nama)) {
                                $operatorName = $ploting->operator->nama;
                                $foundOperator = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            // Calculate current shift actual (simplified)
            if ($currentShift === 'pagi') {
                $actualShiftPagi = $actualHariIni;
            } elseif ($currentShift === 'siang') {
                $actualShiftSiang = $actualHariIni;
            } else {
                $actualShiftMalam = $actualHariIni;
            }

            // Calculate progress percentage
            $progressPercentage = 0;
            if ($targetHariIni > 0) {
                $progressPercentage = min(100, round(($actualHariIni / $targetHariIni) * 100, 1));
            }

            // Set keterangan based on machine status
            if (in_array($mesin->status, ['offline', 'maintenance', 'stop'])) {
                $keterangan = $mesin->stop_reason ?? $mesin->keterangan ?? ($mesin->status === 'maintenance' ? 'Dalam perbaikan' : ($mesin->status === 'offline' ? 'Mesin offline' : 'Mesin berhenti'));
            } elseif ($plannings->isEmpty()) {
                $keterangan = 'Tidak ada planning hari ini';
            }

            // Get current shift target and actual
            $targetCurrentShift = 0;
            $actualCurrentShift = 0;
            if ($currentShift === 'pagi') {
                $targetCurrentShift = $targetShiftPagi;
                $actualCurrentShift = $actualShiftPagi;
            } elseif ($currentShift === 'siang') {
                $targetCurrentShift = $targetShiftSiang;
                $actualCurrentShift = $actualShiftSiang;
            } else {
                $targetCurrentShift = $targetShiftMalam;
                $actualCurrentShift = $actualShiftMalam;
            }

            // FALLBACK: If shift-specific target is 0 but daily target > 0, 
            // it might be a single-planning for the whole day.
            if ($targetCurrentShift == 0 && $targetHariIni > 0) {
                $targetCurrentShift = $targetHariIni;
            }
            if ($actualCurrentShift == 0 && $actualHariIni > 0) {
                $actualCurrentShift = $actualHariIni;
            }

            $history = $machineEvents->map(function ($e) use ($mesin, $rangeStart, $rangeEndFinal) {
                try {
                    $evDate = is_string($e->tanggal) ? $e->tanggal : $e->tanggal->format('Y-m-d');
                    $evtStart = \Carbon\Carbon::parse($evDate . ' ' . $e->start_time);

                    if ($e->end_time) {
                        $evtEnd = \Carbon\Carbon::parse($evDate . ' ' . $e->end_time);
                        if ($evtEnd->lt($evtStart)) $evtEnd->addDay();
                    } else {
                        $evtEnd = $rangeEndFinal->copy();
                    }

                    // Clamp to the current day view
                    $clampedStart = $evtStart->max($rangeStart);
                    $clampedEnd = $evtEnd->min($rangeEndFinal);

                    if ($clampedStart->gte($clampedEnd)) return null;

                    $dType = strtolower($e->downtime_type);
                    $durationSec = $clampedStart->diffInSeconds($clampedEnd);

                    // Determine Reason properly:
                    $reason = '';

                    // 1. First priority: description or keterangan from the event itself
                    $reason = trim($e->description ?: ($e->keterangan ?: ''));

                    // 2. Second priority: If status is broad, try to use downtime_type if it is specific
                    $isBroadStatus = in_array($dType, ['running', 'aktif', 'maintenance', 'breakdown', 'offline', 'tidak aktif', 'stop']);

                    if (empty($reason) && !$isBroadStatus) {
                        // For specific reasons stored in downtime_type (e.g. 'Setup', 'Set Down')
                        $reason = ucwords(str_replace(['_', '-'], ' ', $dType));
                    }

                    // 3. Third priority: If it's a broad status and the event is ongoing, take from machine's current stop_reason
                    if (empty($reason) && !$e->end_time) {
                        $reason = $mesin->stop_reason ?: '';
                    }

                    // 4. Fallback Logic: Ensure there's ALWAYS a human-readable reason
                    if (empty($reason)) {
                        if (in_array($dType, ['running', 'aktif'])) {
                            $reason = 'Mesin Berjalan';
                        } elseif ($dType === 'maintenance' || $dType === 'breakdown') {
                            $reason = 'Breakdown / Perbaikan';
                        } elseif ($dType === 'offline' || $dType === 'tidak aktif') {
                            $reason = 'Mesin Off (Tanpa Plan)';
                        } elseif ($dType === 'stop') {
                            $reason = 'Stand By / Berhenti';
                        } else {
                            $reason = ucwords(str_replace(['_', '-'], ' ', $dType));
                        }
                    }

                    return [
                        'start' => $clampedStart->format('H:i'),
                        'end' => $e->end_time ? $clampedEnd->format('H:i') : 'Sekarang',
                        'type' => $dType,
                        'reason' => $reason,
                        'duration_sec' => $durationSec
                    ];
                } catch (\Exception $ex) {
                    return null;
                }
            })->filter()->values()->toArray();

            // Add virtual ongoing event if no open event exists in DB
            if (!$hasOpenEvent) {
                $statusSince = $mesin->status_since ? \Carbon\Carbon::parse($mesin->status_since) : $rangeStart;
                $effectiveStart = $statusSince->max($rangeStart);

                if ($effectiveStart->lt($rangeEndFinal)) {
                    $virtualDurationSec = $effectiveStart->diffInSeconds($rangeEndFinal);

                    // Determine virtual reason
                    $vReason = $mesin->stop_reason ?: ($mStatus === 'running' ? 'Mesin Berjalan' : ucwords(str_replace('_', ' ', $mStatus)));

                    $history[] = [
                        'start' => $effectiveStart->format('H:i'),
                        'end' => 'Sekarang',
                        'type' => $mStatus,
                        'reason' => $vReason,
                        'duration_sec' => $virtualDurationSec
                    ];
                }
            }

            $data[$mesin->id] = [
                'status' => $mesin->status,
                'stop_reason' => $mesin->stop_reason,
                'status_since' => $mesin->status_since ? \Carbon\Carbon::parse($mesin->status_since)->toIso8601String() : null,
                'target_hari_ini' => $targetHariIni,
                'target_shift_pagi' => $targetShiftPagi,
                'target_shift_siang' => $targetShiftSiang,
                'target_shift_malam' => $targetShiftMalam,
                'target_current_shift' => $targetCurrentShift,
                'actual_hari_ini' => $actualHariIni,
                'actual_shift_pagi' => $actualShiftPagi,
                'actual_shift_siang' => $actualShiftSiang,
                'actual_shift_malam' => $actualShiftMalam,
                'actual_current_shift' => $actualCurrentShift,
                'progress_percentage' => $progressPercentage,
                'keterangan' => $keterangan,
                'part_name' => $partName,
                'customer' => $customer,
                'operator_name' => $operatorName,
                'current_shift' => $currentShift,
                'has_planning' => !$plannings->isEmpty(),
                'total_runtime_today' => $totalJamDisplay,
                'summary' => [
                    'running' => $formatSec($runtimeSec),
                    'standby' => $formatSec($standbySec),
                    'downtime' => $formatSec($downtimeSec),
                    'offline' => $formatSec($offlineSec),
                ],
                'history' => $history
            ];
        }

        $aggregates = [
            'running' => $formatSec($aggRunningSec),
            'standby' => $formatSec($aggStandbySec),
            'downtime' => $formatSec($aggDowntimeSec),
            'offline' => $formatSec($aggOfflineSec)
        ];

        return [
            'machines' => $data,
            'aggregates' => $aggregates
        ];
    }

    /**
     * Get machine documents for example modals
     */
    public function getMachineDocuments($id)
    {
        $mesin = Mesin::findOrFail($id);

        // Default documents
        $defaults = [
            'apd' => [
                'url' => asset('images/barang/StandarAPDinject.pdf'),
                'filename' => 'StandarAPDinject.pdf',
            ],
            'qpoint' => [
                'url' => asset('images/barang/3Qpointrev.pdf'),
                'filename' => '3Qpointrev.pdf',
            ],
            'instruksi_kerja' => [
                'url' => asset('images/barang/intruksikerja.pdf'),
                'filename' => 'intruksikerja.pdf',
            ],
            'limit_sampel' => [
                'url' => asset('images/barang/LimitSampel.pdf'),
                'filename' => 'LimitSampel.pdf',
            ],
            'amptm' => [
                'url' => asset('images/barang/1AMTPM.pdf'),
                'filename' => '1AMTPM.pdf',
            ],
        ];

        // In a real system, you might fetch these from a database table
        // For now, we use the defaults as configured in the admin section

        return response()->json([
            'success' => true,
            'data' => $defaults
        ]);
    }

    /**
     * Get operators by grup_id
     */
    public function getOperatorsByGrup(Request $request)
    {
        $grupId = $request->query('grup_id');

        if (!$grupId) {
            return response()->json(['data' => []]);
        }

        $operators = User::where('role', 'operator')
            ->where('bagian', 'inject')
            ->where('grup_id', $grupId)
            ->get(['id', 'nama', 'username', 'nik']);

        return response()->json([
            'data' => $operators,
            'message' => $operators->count() > 0
                ? "Ditemukan {$operators->count()} operator"
                : "Tidak ada operator di grup ini"
        ]);
    }

    /**
     * Get ploting data for a specific machine
     */
    public function getPlotingByMesin(Request $request)
    {
        $mesinId = $request->query('mesin_id');
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        if (!$mesinId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesin ID harus disediakan',
                'data' => []
            ]);
        }

        // Get planning produksi for this machine on this date
        $plannings = \App\Models\PlanningProduksi::with([
            'plotings' => function ($query) {
                $query->whereNotNull('user_id')
                    ->with(['operator', 'grup']);
            },
            'part'
        ])
            ->where('mesin_id', $mesinId)
            ->whereDate('tanggal', $tanggal)
            ->where('status_approval', 'approved')
            ->get();

        $plotingData = [];
        foreach ($plannings as $planning) {
            foreach ($planning->plotings as $ploting) {
                if ($ploting->user_id) {
                    // Get shift from jadwal kerja
                    $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->user_id)
                        ->whereDate('tanggal', $tanggal)
                        ->where('status', 'aktif')
                        ->first();

                    $isCurrentShift = false;
                    $now = \Carbon\Carbon::now();
                    $currentTime = $now->format('H:i:s');

                    if ($jadwal && $jadwal->jam_masuk && $jadwal->jam_keluar) {
                        $masuk = $jadwal->jam_masuk;
                        $keluar = $jadwal->jam_keluar;

                        if ($keluar < $masuk) { // Night shift
                            if ($currentTime >= $masuk || $currentTime <= $keluar) {
                                $isCurrentShift = true;
                            }
                        } else {
                            if ($currentTime >= $masuk && $currentTime <= $keluar) {
                                $isCurrentShift = true;
                            }
                        }
                    }

                    $shift = null;
                    $shiftDisplay = null;
                    if ($jadwal) {
                        $shiftMap = ['pagi' => 'shift_1', 'siang' => 'shift_2', 'malam' => 'shift_3'];
                        $shift = $shiftMap[$jadwal->shift] ?? null;
                        $shiftDisplay = 'Shift ' . ucfirst($jadwal->shift);
                    }

                    // Check if checksheet already exists
                    $hasChecksheet = false;
                    $existingChecksheet = null;
                    if ($shift) {
                        $cs = Checksheet::where('mesin_id', $mesinId)
                            ->where('user_id', $ploting->user_id)
                            ->whereDate('created_at', $tanggal)
                            ->get()
                            ->filter(function ($item) use ($shift) {
                                return $item->shift === $shift;
                            })
                            ->first();

                        if ($cs) {
                            $hasChecksheet = true;
                            $existingChecksheet = [
                                'id' => $cs->id,
                                'shift' => $cs->shift,
                                'shift_display' => $cs->shift_display,
                                'created_at' => $cs->created_at->format('Y-m-d H:i:s')
                            ];
                        }
                    }

                    // ✅ ONLY add to plotingData if operator is currently in their shift
                    // This prevents showing operators whose shift has already ended
                    if ($isCurrentShift) {
                        $plotingData[] = [
                            'ploting_id' => $ploting->id,
                            'user_id' => $ploting->user_id,
                            'user_nama' => $ploting->operator->nama ?? '-',
                            'grup_id' => $ploting->grup_id,
                            'grup_nama' => $ploting->grup->nama_grup ?? '-',
                            'part_id' => $planning->part_id,
                            'part_nama' => $planning->part->nama_part ?? '-',
                            'part_no' => $planning->part->no_parts ?? '-',
                            'status' => $ploting->status,
                            'is_active' => $ploting->status === 'on_progress',
                            'shift' => $shift,
                            'shift_display' => $shiftDisplay,
                            'has_checksheet' => $hasChecksheet,
                            'existing_checksheet' => $existingChecksheet,
                            'is_current_shift' => $isCurrentShift,
                            'updated_at' => $ploting->updated_at
                        ];
                    }
                }
            }
        }

        // Sort: Current shift first, then active status
        usort($plotingData, function ($a, $b) {
            if ($a['is_current_shift'] && !$b['is_current_shift']) return -1;
            if (!$a['is_current_shift'] && $b['is_current_shift']) return 1;
            if ($a['is_active'] && !$b['is_active']) return -1;
            if (!$a['is_active'] && $b['is_active']) return 1;
            return 0;
        });

        return response()->json([
            'status' => 'success',
            'data' => $plotingData,
            'has_ploting' => count($plotingData) > 0
        ]);
    }
}
