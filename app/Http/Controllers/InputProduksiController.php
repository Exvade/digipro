<?php

namespace App\Http\Controllers;

use App\Models\InputProduksi;
use App\Models\PlanningProduksi;
use App\Models\Ploting;
use App\Models\JadwalKerja;
use App\Models\ProduksiPerJam;
// use App\Models\OeeRecord; // Dihapus - tabel oee_records tidak digunakan
use App\Models\User;
use App\Models\PersiapanProduksiCheck;
use App\Models\DowntimeEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class InputProduksiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        try {
            Log::info('=== DEBUG INPUT PRODUKSI ===');
            Log::info('User ID: ' . $user->id . ' - ' . $user->name);

            // 🔹 Ambil hanya planning sesuai user login (untuk semua role)
            // Input Produksi adalah halaman kerja personal, jadi hanya tampilkan yang ditugaskan
            $plannings = PlanningProduksi::whereHas('plotings', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'on_progress')
                    ->whereDate('tanggal', Carbon::today()); // ✅ Hanya plotting hari ini
            })
                ->with([
                    'mesin',
                    'meja', // ✅ Tambahkan meja untuk assy
                    'part',
                    'moldshop',
                    'plotings.persiapanChecks',
                    'inputProduksi'
                ])
                ->get();

            Log::info('Found plannings: ' . $plannings->count());

            // Get NG Part Types for modal selection
            try {
                $ngPartTypes = \App\Models\NgPartType::active()->ordered()->get();
            } catch (\Exception $e) {
                Log::warning('NgPartType table might not exist yet: ' . $e->getMessage());
                // Fallback: create collection with default NG part types
                $ngPartTypes = collect([
                    (object)['id' => 1, 'name' => 'Short Mold', 'code' => 'SM'],
                    (object)['id' => 2, 'name' => 'Silver', 'code' => 'SV'],
                    (object)['id' => 3, 'name' => 'Weldline', 'code' => 'WL'],
                    (object)['id' => 4, 'name' => 'Hairline', 'code' => 'HL'],
                    (object)['id' => 5, 'name' => 'Burry/Flash', 'code' => 'BF'],
                    (object)['id' => 6, 'name' => 'Crack', 'code' => 'CR'],
                    (object)['id' => 7, 'name' => 'Dirty', 'code' => 'DT'],
                    (object)['id' => 8, 'name' => 'Overcut', 'code' => 'OC'],
                    (object)['id' => 9, 'name' => 'Yellow wish', 'code' => 'YW'],
                    (object)['id' => 10, 'name' => 'Shining', 'code' => 'SH'],
                    (object)['id' => 11, 'name' => 'Scrath', 'code' => 'SC'],
                    (object)['id' => 12, 'name' => 'Blackdot', 'code' => 'BD'],
                    (object)['id' => 13, 'name' => 'Flowmark', 'code' => 'FM'],
                    (object)['id' => 14, 'name' => 'Gasmark/Airtrap', 'code' => 'GA'],
                    (object)['id' => 15, 'name' => 'Sinkmark', 'code' => 'SK'],
                    (object)['id' => 16, 'name' => 'Bending', 'code' => 'BG'],
                    (object)['id' => 17, 'name' => 'Bubble', 'code' => 'BB'],
                    (object)['id' => 18, 'name' => 'Kontaminasi', 'code' => 'KN'],
                    (object)['id' => 19, 'name' => 'Ejector mark', 'code' => 'EM'],
                    (object)['id' => 20, 'name' => 'Whiteness', 'code' => 'WH'],
                    (object)['id' => 21, 'name' => 'Discolour', 'code' => 'DC'],
                ]);
            }

            return view('input-produksi.index', compact('plannings', 'ngPartTypes'));
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            // Ensure ngPartTypes is always available
            $ngPartTypes = collect([]);
            return view('input-produksi.index', ['plannings' => collect(), 'ngPartTypes' => $ngPartTypes]);
        }
    }

    /**
     * Get data for DataTables (Super Admin, Kabag, Foreman, Leader)
     */
    public function getDataTablesData(Request $request)
    {
        $user = Auth::user();

        // Izinkan super_admin, kabag, foreman, dan leader
        if (!in_array($user->role, ['super_admin', 'kabag', 'foreman', 'leader'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $planningTable = (new PlanningProduksi())->getTable();

            $query = InputProduksi::with([
                'planning.part',
                'planning.mesin',
                'planning.moldshop',
                'planning.meja',
                'ploting.user',
                'approver'
            ])->latest();

            // Filter by bagian
            if ($request->filled('bagian')) {
                $bagianFilter = strtolower($request->bagian);

                if (in_array($bagianFilter, ['assy', 'inject'])) {
                    $bagianCaseSql = "COALESCE({$planningTable}.bagian, CASE " .
                        "WHEN {$planningTable}.meja_id IS NOT NULL THEN 'assy' " .
                        "WHEN {$planningTable}.mesin_id IS NOT NULL THEN 'inject' " .
                        "ELSE 'inject' END)";

                    $query->whereHas('planning', function ($planningQuery) use ($bagianFilter, $bagianCaseSql) {
                        $planningQuery->whereRaw("{$bagianCaseSql} = ?", [$bagianFilter]);
                    });
                }
            }

            // Filter berdasarkan role user
            // Leader hanya bisa lihat bagian mereka
            if ($user->role === 'leader' && isset($user->bagian)) {
                $userBagian = strtolower($user->bagian);
                if (in_array($userBagian, ['inject', 'assy'])) {
                    $bagianCaseSql = "COALESCE({$planningTable}.bagian, CASE " .
                        "WHEN {$planningTable}.meja_id IS NOT NULL THEN 'assy' " .
                        "WHEN {$planningTable}.mesin_id IS NOT NULL THEN 'inject' " .
                        "ELSE 'inject' END)";

                    $query->whereHas('planning', function ($planningQuery) use ($userBagian, $bagianCaseSql) {
                        $planningQuery->whereRaw("{$bagianCaseSql} = ?", [$userBagian]);
                    });
                }
            }

            // Foreman hanya bisa lihat data dari grup mereka
            if ($user->role === 'foreman' && isset($user->grup_id)) {
                $query->whereHas('ploting', function ($plotingQuery) use ($user) {
                    $plotingQuery->where('grup_id', $user->grup_id);
                });
            }

            // Apply filters if any
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $data = $query->get()->map(function ($item, $index) {
                $planning = $item->planning;
                $total = ($item->ok_count ?? 0) + ($item->ng_count ?? 0);
                $target = $planning?->qty_planning_produksi ?? 0;
                $gap = max(0, $target - $total);
                $percentage = $target > 0 ? min(100, round(($total / $target) * 100, 1)) : 0;

                // Ambil waktu input terakhir dari produksi_per_jam untuk planning ini
                // Gunakan logika yang sama dengan distribusi di PDF untuk konsistensi
                $lastInputTime = null;
                $lastInputJam = null;
                $lastInputShift = null;
                if ($planning) {
                    // Ambil data produksi_per_jam yang memiliki OK/NG > 0, urutkan berdasarkan waktu update terakhir
                    // Ini akan menunjukkan waktu input terakhir yang sesuai dengan distribusi di PDF
                    $lastProduksiPerJam = \App\Models\ProduksiPerJam::where('planning_id', $planning->id)
                        ->where(function ($q) {
                            $q->where('ok_count', '>', 0)
                                ->orWhere('ng_part', '>', 0);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->first();

                    if ($lastProduksiPerJam) {
                        $lastInputTime = $lastProduksiPerJam->updated_at;
                        $lastInputJam = $lastProduksiPerJam->jam;
                        $lastInputShift = $lastProduksiPerJam->shift;
                    } else {
                        // Jika tidak ada data produksi_per_jam, gunakan waktu dari InputProduksi
                        // Tapi tetap hitung shift dan jam berdasarkan waktu tersebut untuk konsistensi
                        $inputTime = $item->updated_at ?? $item->created_at;
                        if ($inputTime) {
                            $hour = (int) $inputTime->format('H');

                            // Tentukan shift dari jam (sama dengan logika di LaporanProduksiController)
                            if ($hour >= 7 && $hour < 15) {
                                $lastInputShift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $lastInputShift = 'siang';
                            } else {
                                $lastInputShift = 'malam';
                            }

                            // Tentukan jam dari waktu (sama dengan getCurrentJam())
                            $jamAwal = $hour;
                            $jamAkhir = $hour + 1;
                            if ($jamAwal == 23) {
                                $lastInputJam = '23.00 - 24.00';
                            } elseif ($jamAwal == 0) {
                                $lastInputJam = '24.00 - 01.00';
                            } elseif ($jamAwal >= 1 && $jamAwal <= 6) {
                                $lastInputJam = sprintf('%02d.00 - %02d.00', $jamAwal, $jamAkhir);
                            } else {
                                $lastInputJam = sprintf('%02d.00 - %02d.00', $jamAwal, $jamAkhir);
                            }

                            $lastInputTime = $inputTime;
                        }
                    }
                }

                // Format waktu input terakhir
                $waktuInput = '-';
                if ($lastInputTime) {
                    $shiftDisplay = '';
                    switch ($lastInputShift) {
                        case 'pagi':
                            $shiftDisplay = 'Shift 1';
                            break;
                        case 'siang':
                            $shiftDisplay = 'Shift 2';
                            break;
                        case 'malam':
                            $shiftDisplay = 'Shift 3';
                            break;
                    }
                    $waktuInput = $lastInputTime->format('d/m/Y H:i') . '<br><small class="text-muted">' . $shiftDisplay . ' - ' . $lastInputJam . '</small>';
                } elseif ($item->updated_at) {
                    // Fallback ke updated_at dari InputProduksi jika tidak ada data produksi_per_jam
                    $waktuInput = $item->updated_at->format('d/m/Y H:i');
                }

                // Status badge
                $statusBadge = '';
                if ($item->status === 'approved') {
                    $statusBadge = '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Approved</span>';
                } elseif ($item->status === 'rejected') {
                    $statusBadge = '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
                } else {
                    $statusBadge = '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Pending</span>';
                }

                // Progress bar
                $progressColor = $percentage >= 100 ? 'success' : ($percentage >= 70 ? 'info' : 'warning');
                $progressBar = '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-' . $progressColor . '" role="progressbar" 
                         style="width: ' . $percentage . '%" 
                         aria-valuenow="' . $percentage . '" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        ' . $percentage . '%
                    </div>
                </div>';

                // Action buttons
                $actions = '<div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-info btn-view" 
                            data-id="' . $item->id . '" 
                            title="View Detail">
                        <i class="fas fa-eye"></i>
                    </button>';

                if ($item->status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-approve" 
                                data-id="' . $item->id . '" 
                                title="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-reject" 
                                data-id="' . $item->id . '" 
                                title="Reject">
                            <i class="fas fa-times"></i>
                        </button>';
                }

                $actions .= '</div>';

                // Determine mesin/meja based on bagian
                $mesinOrMeja = 'N/A';
                $moldName = 'N/A';
                $bagianBadge = '<span class="badge badge-secondary">N/A</span>';

                if ($planning) {
                    $resolvedBagian = $this->resolveBagian($planning);

                    if ($resolvedBagian === 'assy') {
                        $bagianBadge = '<span class="badge badge-info">Assy</span>';
                        $mesinOrMeja = $planning->meja?->nama_meja ?? 'N/A';
                        $moldName = '-'; // Assy tidak punya mold
                    } else {
                        $bagianBadge = '<span class="badge badge-primary">Inject</span>';
                        $mesinOrMeja = $planning->mesin?->nama_mesin ?? 'N/A';
                        $moldName = $planning->moldshop?->nama_mold ?? 'N/A';
                    }
                }

                return [
                    'DT_RowId' => 'row_' . $item->id,
                    'no' => $index + 1,
                    'bagian' => $bagianBadge,
                    'tanggal' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-',
                    'waktu_input' => $waktuInput,
                    'part_code' => $planning?->part?->no_parts ?? 'N/A',
                    'part_name' => $planning?->part?->nama_part ?? 'N/A',
                    'mesin' => $mesinOrMeja,
                    'mold' => $moldName,
                    'target' => number_format($target),
                    'ok' => number_format($item->ok_count ?? 0),
                    'ng' => number_format($item->ng_count ?? 0),
                    'total' => number_format($total),
                    'gap' => number_format($gap),
                    'progress' => $progressBar,
                    'status' => $statusBadge,
                    'actions' => $actions,
                ];
            });

            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataTablesData: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading data'], 500);
        }
    }

    private function resolveBagian(?PlanningProduksi $planning): string
    {
        if (!$planning) {
            return 'inject';
        }

        $bagian = strtolower($planning->bagian ?? '');

        if (in_array($bagian, ['assy', 'inject'])) {
            return $bagian;
        }

        if (!is_null($planning->meja_id) || $planning->meja) {
            return 'assy';
        }

        return 'inject';
    }

    /**
     * Tentukan shift aktif berdasarkan jam sekarang
     * Shift 1 (Pagi): 07:00 - 15:00
     * Shift 2 (Siang): 15:00 - 23:00
     * Shift 3 (Malam): 23:00 - 07:00
     */
    private function getCurrentShift()
    {
        $currentHour = (int) now()->format('H');

        if ($currentHour >= 7 && $currentHour < 15) {
            return 'pagi'; // Shift 1
        } elseif ($currentHour >= 15 && $currentHour < 23) {
            return 'siang'; // Shift 2
        } else {
            return 'malam'; // Shift 3 (23:00 - 07:00)
        }
    }

    /**
     * Tentukan jam aktif saat ini dalam format "HH.00 - HH.00"
     * Contoh: Jika sekarang 08:30, return "08.00 - 09.00"
     * Format harus sesuai dengan form: "07.00 - 08.00", "08.00 - 09.00", "24.00 - 01.00", dll
     */
    private function getCurrentJam()
    {
        $now = now();
        $currentHour = (int) $now->format('H');

        // Tentukan jam awal berdasarkan jam saat ini
        $jamAwal = $currentHour;
        $jamAkhir = $currentHour + 1;

        // Handle shift malam yang melintasi hari
        // Format khusus untuk shift malam: "23.00 - 24.00", "24.00 - 01.00", "01.00 - 02.00", dll
        if ($jamAwal == 23) {
            // Jam 23:00 - 24:00
            return '23.00 - 24.00';
        } elseif ($jamAwal == 0) {
            // Jam 00:00 - 01:00 (format: "24.00 - 01.00")
            return '24.00 - 01.00';
        } elseif ($jamAwal >= 1 && $jamAwal <= 6) {
            // Jam 01:00 - 07:00 (format: "01.00 - 02.00", "02.00 - 03.00", dll)
            return sprintf('%02d.00 - %02d.00', $jamAwal, $jamAkhir);
        } else {
            // Jam normal 07:00 - 23:00
            return sprintf('%02d.00 - %02d.00', $jamAwal, $jamAkhir);
        }
    }

    /**
     * Simpan atau update produksi per jam untuk jam aktif saat ini
     */
    private function saveProduksiPerJamRealtime($planningId, $plotingId, $inputProduksiId, $currentShift, $currentJam, $isOk = true)
    {
        try {
            // Cari data produksi per jam untuk jam aktif ini
            $produksiPerJam = ProduksiPerJam::where('planning_id', $planningId)
                ->where('shift', $currentShift)
                ->where('jam', $currentJam)
                ->first();

            if ($produksiPerJam) {
                // Update data yang sudah ada
                if ($isOk) {
                    $produksiPerJam->increment('ok_count');
                } else {
                    $produksiPerJam->increment('ng_part');
                }
            } else {
                // Buat data baru
                $user = Auth::user();
                $produksiPerJam = ProduksiPerJam::create([
                    'planning_id' => $planningId,
                    'input_produksi_id' => $inputProduksiId,
                    'ploting_id' => $plotingId,
                    'shift' => $currentShift,
                    'jam' => $currentJam,
                    'target' => 0, // Target bisa diisi manual di form
                    'ok_count' => $isOk ? 1 : 0,
                    'ng_part' => $isOk ? 0 : 1,
                    'operator_code' => $user->nik ?? null,
                ]);
            }

            // Log dengan detail waktu untuk tracking
            $now = now();
            Log::info('Produksi per jam updated (realtime) - Setiap klik OK/NG tersimpan ke jam yang tepat', [
                'planning_id' => $planningId,
                'shift' => $currentShift,
                'jam' => $currentJam,
                'is_ok' => $isOk,
                'produksi_per_jam_id' => $produksiPerJam->id,
                'waktu_klik' => $now->format('Y-m-d H:i:s'),
                'ok_count' => $produksiPerJam->ok_count,
                'ng_part' => $produksiPerJam->ng_part,
                'message' => 'Data ini akan muncul di PDF di jam: ' . $currentJam . ' shift: ' . $currentShift
            ]);

            return $produksiPerJam;
        } catch (\Exception $e) {
            Log::error('Error saving produksi per jam realtime: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hitung total input per shift untuk planning tertentu
     * Note: Ini adalah estimasi berdasarkan waktu input, karena input_produksi tidak menyimpan shift
     * Untuk akurasi lebih baik, bisa ditambahkan kolom shift di input_produksi
     */
    private function getTotalInputPerShift($planningId, $shift)
    {
        // Untuk sementara, kita akan menggunakan pendekatan:
        // Jika shift aktif adalah pagi, maka kita batasi berdasarkan target_shift_pagi
        // Total input dihitung dari semua input produksi untuk planning ini
        // Tapi kita perlu cara untuk membedakan input per shift

        // Sementara ini, kita akan menggunakan total input hari ini
        // dan membatasi berdasarkan target shift yang aktif
        $totalInput = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
            $q->where('planning_id', $planningId);
        })
            ->whereDate('created_at', Carbon::today())
            ->sum(DB::raw('ok_count + ng_count'));

        return $totalInput;
    }

    /**
     * Hitung total input per shift berdasarkan waktu updated_at
     * Karena ok_count dan ng_count adalah kumulatif per record, kita perlu menjumlahkan
     * total dari semua ploting untuk planning ini yang updated dalam shift tersebut.
     */
    private function getTotalInputByShift($planningId, $shift)
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $query = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
            $q->where('planning_id', $planningId);
        });

        // Filter berdasarkan waktu updated_at untuk menentukan shift
        if ($shift === 'pagi') {
            // Shift pagi: 07:00 - 15:00
            // Jumlahkan total dari semua ploting yang updated dalam shift pagi
            $totalInput = $query->whereDate('updated_at', $today)
                ->whereTime('updated_at', '>=', '07:00:00')
                ->whereTime('updated_at', '<', '15:00:00')
                ->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        } elseif ($shift === 'siang') {
            // Shift siang: 15:00 - 23:00
            $totalInput = $query->whereDate('updated_at', $today)
                ->whereTime('updated_at', '>=', '15:00:00')
                ->whereTime('updated_at', '<', '23:00:00')
                ->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        } elseif ($shift === 'malam') {
            // Shift malam: 23:00 - 07:00 (melintasi hari)
            $totalInput = $query->where(function ($q) use ($today, $tomorrow) {
                $q->where(function ($subQ) use ($today) {
                    // Hari ini 23:00 - 23:59:59
                    $subQ->whereDate('updated_at', $today)
                        ->whereTime('updated_at', '>=', '23:00:00');
                })->orWhere(function ($subQ) use ($tomorrow) {
                    // Besok 00:00:00 - 07:00:00
                    $subQ->whereDate('updated_at', $tomorrow)
                        ->whereTime('updated_at', '<', '07:00:00');
                });
            })->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        }

        return 0;
    }

    /**
     * Cek apakah target shift sudah tercapai untuk ploting tertentu
     */
    private function checkShiftTargetReached($ploting, $planning, $currentShift)
    {
        if (!$ploting || !$planning) {
            return false;
        }

        // Ambil target shift
        $targetShift = 0;
        switch ($currentShift) {
            case 'pagi':
                $targetShift = $planning->target_shift_pagi ?? 0;
                break;
            case 'siang':
                $targetShift = $planning->target_shift_siang ?? 0;
                break;
            case 'malam':
                $targetShift = $planning->target_shift_malam ?? 0;
                break;
        }

        if ($targetShift == 0) {
            return false; // Tidak ada target untuk shift ini
        }

        // Hitung total input untuk shift ini
        $totalInputShift = $this->getTotalInputByShift($planning->id, $currentShift);

        // Jika total input sudah mencapai atau melebihi target
        return $totalInputShift >= $targetShift;
    }

    /**
     * Validasi apakah masih bisa input berdasarkan target shift
     */
    private function validateShiftTarget($planning, $currentShift)
    {
        $targetShift = 0;

        switch ($currentShift) {
            case 'pagi':
                $targetShift = $planning->target_shift_pagi ?? 0;
                break;
            case 'siang':
                $targetShift = $planning->target_shift_siang ?? 0;
                break;
            case 'malam':
                $targetShift = $planning->target_shift_malam ?? 0;
                break;
        }

        // Jika target shift = 0, berarti tidak ada target untuk shift ini
        if ($targetShift == 0) {
            return [
                'allowed' => false,
                'message' => "Tidak ada target produksi untuk shift {$currentShift}. Silakan hubungi staff PPIC."
            ];
        }

        // Hitung total input untuk shift yang sedang aktif
        $totalInputShift = $this->getTotalInputByShift($planning->id, $currentShift);

        // Untuk shift 1 (pagi), batasi sesuai target_shift_pagi
        // Jangan sampai melebihi target shift pagi
        if ($currentShift === 'pagi') {
            if ($totalInputShift >= $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift pagi ({$targetShift}) sudah tercapai. Input saat ini: {$totalInputShift}. Tidak bisa menambah input lagi."
                ];
            }

            // Cek juga apakah setelah input ini akan melebihi target
            if (($totalInputShift + 1) > $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift pagi ({$targetShift}) akan terlampaui. Input saat ini: {$totalInputShift}. Sisa: " . ($targetShift - $totalInputShift)
                ];
            }
        }

        // Untuk shift 2 (siang), batasi sesuai target_shift_siang
        if ($currentShift === 'siang') {
            if ($totalInputShift >= $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift siang ({$targetShift}) sudah tercapai. Input saat ini: {$totalInputShift}. Tidak bisa menambah input lagi."
                ];
            }

            if (($totalInputShift + 1) > $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift siang ({$targetShift}) akan terlampaui. Input saat ini: {$totalInputShift}. Sisa: " . ($targetShift - $totalInputShift)
                ];
            }
        }

        // Untuk shift 3 (malam), batasi sesuai target_shift_malam
        if ($currentShift === 'malam') {
            if ($totalInputShift >= $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift malam ({$targetShift}) sudah tercapai. Input saat ini: {$totalInputShift}. Tidak bisa menambah input lagi."
                ];
            }

            if (($totalInputShift + 1) > $targetShift) {
                return [
                    'allowed' => false,
                    'message' => "Target shift malam ({$targetShift}) akan terlampaui. Input saat ini: {$totalInputShift}. Sisa: " . ($targetShift - $totalInputShift)
                ];
            }
        }

        return [
            'allowed' => true,
            'current_total' => $totalInputShift,
            'target_shift' => $targetShift,
            'remaining' => $targetShift - $totalInputShift
        ];
    }

    private function cekShiftAktif($userId, $ploting)
    {
        $jadwal = JadwalKerja::where('user_id', $userId)
            ->whereDate('tanggal', Carbon::today())
            ->where('status', 'aktif')
            ->first();

        if (!$jadwal) {
            return null; // tidak ada jadwal kerja
        }

        $jamMasuk = Carbon::parse($jadwal->jam_masuk);
        $jamKeluar = Carbon::parse($jadwal->jam_keluar);

        // Shift malam → jam keluar lebih kecil dari jam masuk → tambahkan 1 hari
        if ($jamKeluar->lessThan($jamMasuk)) {
            $jamKeluar->addDay();
        }

        // Kalau sudah lewat jam keluar → shift expired
        // Tapi status tetap on_progress, hanya mencegah input lebih lanjut
        if (now()->greaterThan($jamKeluar)) {
            // Jangan ubah status, tetap on_progress
            // Hanya return false untuk mencegah input lebih lanjut
            return false;
        }

        return true;
    }

    // ===================== REVISI addOk dengan Auto Submit =====================
    public function addOk($planningId)
    {
        $user = Auth::user();
        Log::info('addOk called', [
            'planning_id' => $planningId,
            'user_id' => $user->id
        ]);

        try {
            // Cek planning exists
            $planning = PlanningProduksi::find($planningId);
            if (!$planning) {
                return response()->json([
                    'success' => false,
                    'message' => 'Planning tidak ditemukan'
                ], 404);
            }

            // Cek ploting untuk user ini
            $ploting = Ploting::where('planning_id', $planningId)
                ->where('user_id', $user->id)
                ->first();

            if (!$ploting) {
                Log::warning('Ploting not found', [
                    'planning_id' => $planningId,
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke planning ini'
                ], 403);
            }

            // Cek status ploting
            if ($ploting->status !== 'on_progress') {
                return response()->json([
                    'success' => false,
                    'message' => "Planning tidak aktif. Status: {$ploting->status}"
                ], 400);
            }

            // Cek shift aktif
            $shiftAktif = $this->cekShiftAktif($user->id, $ploting);
            if ($shiftAktif === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift sudah berakhir, tidak bisa menambahkan produk'
                ], 400);
            }

            if ($shiftAktif === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal kerja aktif untuk hari ini'
                ], 400);
            }

            // Tentukan shift aktif berdasarkan jam sekarang
            $currentShift = $this->getCurrentShift();

            // Validasi target shift (khusus untuk shift 1/pagi)
            $shiftValidation = $this->validateShiftTarget($planning, $currentShift);
            if (!$shiftValidation['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $shiftValidation['message']
                ], 400);
            }

            DB::beginTransaction();

            // Create atau update input produksi
            $input = InputProduksi::firstOrCreate(
                ['ploting_id' => $ploting->id],
                [
                    'planning_id' => $planningId,
                    'ok_count' => 0,
                    'ng_count' => 0,
                    'status' => 'pending' // Status awal (menunggu approval)
                ]
            );

            $input->increment('ok_count');

            // ✅ Simpan ke produksi per jam untuk jam aktif saat ini (REALTIME)
            $currentJam = $this->getCurrentJam();
            $this->saveProduksiPerJamRealtime($planningId, $ploting->id, $input->id, $currentShift, $currentJam, true);

            // Hitung total dari semua ploting untuk planning ini
            $okTotal = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                $q->where('planning_id', $planningId);
            })->sum('ok_count');

            $ngTotal = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                $q->where('planning_id', $planningId);
            })->sum('ng_count');

            $totalProduced = $okTotal + $ngTotal;

            // ✅ Cek apakah target shift sudah tercapai → auto-finish ploting
            $shiftTargetReached = $this->checkShiftTargetReached($ploting, $planning, $currentShift);
            if ($shiftTargetReached && $ploting->status === 'on_progress') {
                $startTime = $ploting->start_time ? Carbon::parse($ploting->start_time) : now();
                $runTimeSeconds = max(1, now()->diffInSeconds($startTime));

                $ploting->update([
                    'status' => 'done',
                    'end_time' => now(),
                    'run_time' => $runTimeSeconds,
                ]);

                Log::info('Ploting auto-finished karena target shift tercapai', [
                    'ploting_id' => $ploting->id,
                    'planning_id' => $planningId,
                    'shift' => $currentShift,
                    'run_time' => $runTimeSeconds
                ]);
            }

            // ✅ CRITICAL: Cek jika sudah mencapai target → otomatis submit ke pending
            if ($totalProduced >= $planning->qty_planning_produksi) {
                // Update semua input produksi untuk planning ini ke status pending
                InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                    $q->where('planning_id', $planningId);
                })->where('status', 'pending')->update([
                    'submitted_at' => now()
                ]);

                // Update semua ploting untuk planning ini menjadi completed
                Ploting::where('planning_id', $planningId)
                    ->where('status', 'on_progress')
                    ->update([
                        'status' => 'completed',
                        'end_time' => now()
                    ]);

                Log::info('Production target reached, auto submitted to pending', [
                    'planning_id' => $planningId,
                    'total_produced' => $totalProduced,
                    'target' => $planning->qty_planning_produksi
                ]);

                $message = 'Target produksi tercapai! Data otomatis dikirim untuk approval leader.';
            } else {
                $message = 'Produk OK berhasil ditambahkan';
            }

            DB::commit();

            Log::info('OK count updated successfully', [
                'ok_total' => $okTotal,
                'ng_total' => $ngTotal,
                'status' => $input->status
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'ok_count' => (int) $okTotal,
                    'ng_count' => (int) $ngTotal,
                    'total' => (int) $totalProduced,
                    'target' => (int) $planning->qty_planning_produksi,
                    'status' => $input->status,
                    'is_completed' => $totalProduced >= $planning->qty_planning_produksi
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in addOk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===================== REVISI addNg dengan Auto Submit =====================
    public function addNg($planningId)
    {
        $user = Auth::user();
        Log::info('addNg called with planningId: ' . $planningId . ' by user ' . $user->id);

        try {
            $planning = PlanningProduksi::find($planningId);
            if (!$planning) {
                return response()->json(['success' => false, 'message' => 'Planning tidak ditemukan'], 404);
            }

            $ploting = Ploting::where('planning_id', $planningId)
                ->where('user_id', $user->id)
                ->first();

            if (!$ploting) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke planning ini'], 403);
            }

            // Cek shift
            $shiftAktif = $this->cekShiftAktif($user->id, $ploting);
            if ($shiftAktif === false) {
                return response()->json(['success' => false, 'message' => 'Shift sudah berakhir, tidak bisa menambahkan produk'], 400);
            }

            if ($shiftAktif === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal kerja aktif untuk hari ini'
                ], 400);
            }

            if ($ploting->status !== 'on_progress') {
                return response()->json(['success' => false, 'message' => 'Status planning tidak valid: ' . $ploting->status], 400);
            }

            // Tentukan shift aktif berdasarkan jam sekarang
            $currentShift = $this->getCurrentShift();

            // Validasi target shift (khusus untuk shift 1/pagi)
            $shiftValidation = $this->validateShiftTarget($planning, $currentShift);
            if (!$shiftValidation['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $shiftValidation['message']
                ], 400);
            }

            DB::beginTransaction();

            $input = InputProduksi::firstOrCreate(
                ['ploting_id' => $ploting->id],
                [
                    'planning_id' => $planningId,
                    'ok_count'    => 0,
                    'ng_count'    => 0,
                    'status'      => 'pending' // Status awal (menunggu approval)
                ]
            );

            // Increment general NG
            $input->increment('ng_count');

            // ✅ Simpan ke produksi per jam untuk jam aktif saat ini (REALTIME)
            $currentJam = $this->getCurrentJam();
            $this->saveProduksiPerJamRealtime($planningId, $ploting->id, $input->id, $currentShift, $currentJam, false);

            // Increment category-specific NG if provided
            $category = request('category');
            $ngPartTypeId = request('ng_part_type_id');

            $columnMap = [
                'material' => 'ng_material',
                'process' => 'ng_process',
                'machine' => 'ng_machine',
                'operator' => 'ng_operator'
            ];

            if ($category && isset($columnMap[$category])) {
                $column = $columnMap[$category];
                InputProduksi::where('id', $input->id)->update([
                    $column => DB::raw("$column + 1")
                ]);
            }

            // Save NG Part Type detail if provided
            if ($ngPartTypeId) {
                $ngPartType = \App\Models\NgPartType::find($ngPartTypeId);
                if ($ngPartType) {
                    // Check if already exists, increment quantity, otherwise create new
                    $existingNgPart = DB::table('input_produksi_ng_parts')
                        ->where('input_produksi_id', $input->id)
                        ->where('ng_part_type_id', $ngPartTypeId)
                        ->first();

                    if ($existingNgPart) {
                        // Increment quantity
                        DB::table('input_produksi_ng_parts')
                            ->where('input_produksi_id', $input->id)
                            ->where('ng_part_type_id', $ngPartTypeId)
                            ->update([
                                'quantity' => DB::raw('quantity + 1'),
                                'updated_at' => now()
                            ]);
                    } else {
                        // Create new record
                        DB::table('input_produksi_ng_parts')->insert([
                            'input_produksi_id' => $input->id,
                            'ng_part_type_id' => $ngPartTypeId,
                            'quantity' => 1,
                            'category' => $category,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            $okTotal = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                $q->where('planning_id', $planningId);
            })->sum('ok_count');

            $ngTotal = InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                $q->where('planning_id', $planningId);
            })->sum('ng_count');

            $totalProduced = $okTotal + $ngTotal;

            // ✅ Cek apakah target shift sudah tercapai → auto-finish ploting
            $shiftTargetReached = $this->checkShiftTargetReached($ploting, $planning, $currentShift);
            if ($shiftTargetReached && $ploting->status === 'on_progress') {
                $startTime = $ploting->start_time ? Carbon::parse($ploting->start_time) : now();
                $runTimeSeconds = max(1, now()->diffInSeconds($startTime));

                $ploting->update([
                    'status' => 'done',
                    'end_time' => now(),
                    'run_time' => $runTimeSeconds,
                ]);

                Log::info('Ploting auto-finished karena target shift tercapai (via NG)', [
                    'ploting_id' => $ploting->id,
                    'planning_id' => $planningId,
                    'shift' => $currentShift,
                    'run_time' => $runTimeSeconds
                ]);
            }

            // ✅ CRITICAL: Cek jika sudah mencapai target → otomatis submit ke pending
            if ($totalProduced >= $planning->qty_planning_produksi) {
                // Update semua input produksi untuk planning ini ke status pending
                InputProduksi::whereHas('ploting', function ($q) use ($planningId) {
                    $q->where('planning_id', $planningId);
                })->where('status', 'pending')->update([
                    'submitted_at' => now()
                ]);

                // Update semua ploting untuk planning ini menjadi completed
                Ploting::where('planning_id', $planningId)
                    ->where('status', 'on_progress')
                    ->update([
                        'status' => 'completed',
                        'end_time' => now()
                    ]);

                Log::info('Production target reached via NG, auto submitted to pending', [
                    'planning_id' => $planningId,
                    'total_produced' => $totalProduced,
                    'target' => $planning->qty_planning_produksi
                ]);

                $message = 'Target produksi tercapai! Data otomatis dikirim untuk approval leader.';
            } else {
                $message = 'Produk NG berhasil ditambahkan';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'ok_count' => (int) $okTotal,
                    'ng_count' => (int) $ngTotal,
                    'total' => (int) $totalProduced,
                    'target' => (int) $planning->qty_planning_produksi,
                    'status' => $input->status,
                    'is_completed' => $totalProduced >= $planning->qty_planning_produksi
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in addNg: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Support Call (Andon) from Operator
     */
    public function addCall(Request $request, $planningId)
    {
        $user = Auth::user();
        $category = $request->input('category', 'unknown');

        Log::info('addCall called', [
            'planning_id' => $planningId,
            'user_id' => $user->id,
            'category' => $category
        ]);

        try {
            $planning = PlanningProduksi::with('mesin')->find($planningId);
            if (!$planning) {
                return response()->json(['success' => false, 'message' => 'Planning tidak ditemukan'], 404);
            }

            // Close any existing open events for this machine first to avoid leaks
            DowntimeEvent::where('mesin_id', $planning->mesin_id)
                ->whereNull('end_time')
                ->update([
                    'end_time' => now()->format('H:i:s'),
                    'duration_minutes' => DB::raw('TIMESTAMPDIFF(MINUTE, CONCAT(tanggal, " ", start_time), NOW())'),
                    'status' => 'active' // Mark as handled by a new event or simply closed
                ]);

            // Record this as a DowntimeEvent (Support Call / Standby)
            // This will make it visible on the Leader's dashboard history/history-print
            $downtime = DowntimeEvent::create([
                'mesin_id' => $planning->mesin_id,
                'user_id' => $user->id,
                'tanggal' => now()->toDateString(),
                'shift' => $this->getCurrentShift(),
                'start_time' => now()->format('H:i:s'),
                'downtime_type' => 'stop', // Use 'stop' or 'standby'
                'status' => 'pending', // Waiting for leader/support to resolve
                'title' => 'CALL: ' . strtoupper($category),
                'description' => "Operator {$user->nama} memanggil tim support category: {$category}.",
                'keterangan' => 'Panggilan bantuan (Andon) dari halaman operator.'
            ]);

            Log::info('Support call recorded as DowntimeEvent', ['event_id' => $downtime->id]);

            return response()->json([
                'success' => true,
                'message' => 'Panggilan bantuan berhasil dikirim dan dicatat.',
                'data' => [
                    'event_id' => $downtime->id,
                    'category' => $category
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in addCall: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat panggilan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentData($planningId)
    {
        try {
            $planning = PlanningProduksi::with(['part', 'moldshop', 'inputProduksi'])
                ->find($planningId);

            if (!$planning) {
                return response()->json(['error' => 'Planning tidak ditemukan'], 404);
            }

            $input = $planning->inputProduksi;
            $ok = $input ? $input->ok_count : 0;
            $ng = $input ? $input->ng_count : 0;
            $total = $ok + $ng;
            $gap = max(0, $planning->qty_planning_produksi - $total);
            $percentage = $planning->qty_planning_produksi > 0
                ? min(100, round(($total / $planning->qty_planning_produksi) * 100, 2))
                : 0;
            $efficiency = $total > 0 ? round(($ok / $total) * 100, 1) : 100;

            return response()->json([
                'ok' => $ok,
                'ng' => $ng,
                'gap' => $gap,
                'percentage' => $percentage,
                'efficiency' => $efficiency,
                'part_code' => $planning->part->no_parts ?? '-',
                'part_name' => $planning->part->nama_part ?? '-',
                'mold_no' => $planning->moldshop->no_mold ?? '-',
                'mold_name' => $planning->moldshop->nama_mold ?? '-',
                'customer' => $planning->moldshop->customer ?? '-',
                'planned_qty' => $planning->qty_planning_produksi,
                'status' => $input ? $input->status : 'pending'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCurrentData: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    /**
     * Get data produksi per jam untuk planning tertentu
     */
    public function getProduksiPerJam($planningId)
    {
        try {
            $user = Auth::user();
            $planning = PlanningProduksi::findOrFail($planningId);

            // Tentukan shift operator saat ini
            $currentShift = null;
            if (!in_array($user->role, ['super_admin', 'kabag'])) {
                $jadwal = JadwalKerja::where('user_id', $user->id)
                    ->whereDate('tanggal', \Carbon\Carbon::today())
                    ->where('status', 'aktif')
                    ->first();

                if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                    $currentShift = strtolower($jadwal->shift);
                } else {
                    $currentShift = $this->getCurrentShift();
                }
            } else {
                $currentShift = $this->getCurrentShift();
            }

            // Ambil semua data produksi per jam untuk planning ini
            $produksiPerJam = ProduksiPerJam::where('planning_id', $planningId)
                ->orderBy('shift')
                ->orderBy('jam')
                ->get();

            // Format data per shift dan jam
            $dataByShift = [
                'pagi' => [],
                'siang' => [],
                'malam' => []
            ];

            foreach ($produksiPerJam as $item) {
                $dataByShift[$item->shift][$item->jam] = [
                    'id' => $item->id,
                    'target' => $item->target,
                    'ok_count' => $item->ok_count,
                    'ng_part' => $item->ng_part,
                    'operator_code' => $item->operator_code,
                    'ng_subpart_1' => $item->ng_subpart_1,
                    'ng_subpart_2' => $item->ng_subpart_2,
                    'ng_subpart_3' => $item->ng_subpart_3,
                    'ng_subpart_4' => $item->ng_subpart_4,
                    'ng_subpart_5' => $item->ng_subpart_5,
                    'ng_subpart_6' => $item->ng_subpart_6,
                    'ng_subpart_7' => $item->ng_subpart_7,
                    'masalah' => $item->masalah,
                    'tindakan' => $item->tindakan,
                    'checker_prd' => $item->checker_prd,
                    'checker_qc' => $item->checker_qc,
                    'updated_at' => $item->updated_at ? $item->updated_at->toISOString() : null, // Tambahkan waktu update untuk ditampilkan
                ];
            }

            return response()->json([
                'success' => true,
                'current_shift' => $currentShift,
                'data' => $dataByShift
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getProduksiPerJam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan data produksi per jam
     */
    public function saveProduksiPerJam(Request $request, $planningId)
    {
        try {
            $user = Auth::user();
            $planning = PlanningProduksi::findOrFail($planningId);

            $validated = $request->validate([
                'shift' => 'required|in:pagi,siang,malam',
                'jam' => 'required|string',
                'target' => 'nullable|integer|min:0',
                'ok_count' => 'nullable|integer|min:0',
                'ng_part' => 'nullable|integer|min:0',
                'operator_code' => 'nullable|string|max:50',
                'ng_subpart_1' => 'nullable|integer|min:0',
                'ng_subpart_2' => 'nullable|integer|min:0',
                'ng_subpart_3' => 'nullable|integer|min:0',
                'ng_subpart_4' => 'nullable|integer|min:0',
                'ng_subpart_5' => 'nullable|integer|min:0',
                'ng_subpart_6' => 'nullable|integer|min:0',
                'ng_subpart_7' => 'nullable|integer|min:0',
                'masalah' => 'nullable|string',
                'tindakan' => 'nullable|string',
                'checker_prd' => 'nullable|string|max:100',
                'checker_qc' => 'nullable|string|max:100',
            ]);

            // Ambil ploting dan input produksi untuk user saat ini
            $ploting = null;
            $inputProduksi = null;

            if (!in_array($user->role, ['super_admin', 'kabag'])) {
                $ploting = Ploting::where('planning_id', $planningId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($ploting) {
                    $inputProduksi = InputProduksi::where('ploting_id', $ploting->id)->first();
                }
            }

            // Cari atau buat data produksi per jam
            $produksiPerJam = ProduksiPerJam::updateOrCreate(
                [
                    'planning_id' => $planningId,
                    'shift' => $validated['shift'],
                    'jam' => $validated['jam']
                ],
                [
                    'input_produksi_id' => $inputProduksi?->id,
                    'ploting_id' => $ploting?->id,
                    'target' => $validated['target'] ?? 0,
                    'ok_count' => $validated['ok_count'] ?? 0,
                    'ng_part' => $validated['ng_part'] ?? 0,
                    'operator_code' => $validated['operator_code'] ?? ($user->nik ?? null),
                    'ng_subpart_1' => $validated['ng_subpart_1'] ?? 0,
                    'ng_subpart_2' => $validated['ng_subpart_2'] ?? 0,
                    'ng_subpart_3' => $validated['ng_subpart_3'] ?? 0,
                    'ng_subpart_4' => $validated['ng_subpart_4'] ?? 0,
                    'ng_subpart_5' => $validated['ng_subpart_5'] ?? 0,
                    'ng_subpart_6' => $validated['ng_subpart_6'] ?? 0,
                    'ng_subpart_7' => $validated['ng_subpart_7'] ?? 0,
                    'masalah' => $validated['masalah'] ?? null,
                    'tindakan' => $validated['tindakan'] ?? null,
                    'checker_prd' => $validated['checker_prd'] ?? null,
                    'checker_qc' => $validated['checker_qc'] ?? null,
                ]
            );

            Log::info('Produksi per jam saved', [
                'planning_id' => $planningId,
                'shift' => $validated['shift'],
                'jam' => $validated['jam'],
                'produksi_per_jam_id' => $produksiPerJam->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data produksi per jam berhasil disimpan',
                'data' => $produksiPerJam
            ]);
        } catch (\Exception $e) {
            Log::error('Error in saveProduksiPerJam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===================== APPROVAL METHODS =====================

    /**
     * Get data untuk approval leader (Leader, Foreman, Kabag & Super Admin)
     */
    public function getApprovalData(Request $request)
    {
        $user = Auth::user();

        // Get date filters
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();


        if (!in_array($user->role, ['leader', 'foreman', 'kabag', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            Log::info('=== GET APPROVAL DATA ===');

            // Ambil semua data pending dengan eager loading
            $pendingQuery = InputProduksi::where('status', 'pending')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with([
                    'ploting.user.grup',
                    'ploting.operator.grup',
                    'planning.part',
                    'planning.moldshop',
                    'planning.mesin',
                    'planning.meja'
                ]);

            // Filter berdasarkan role
            // Leader hanya bisa lihat bagian mereka
            if ($user->role === 'leader' && isset($user->bagian)) {
                $userBagian = strtolower($user->bagian);
                if (in_array($userBagian, ['inject', 'assy'])) {
                    $pendingQuery->whereHas('planning', function ($q) use ($userBagian) {
                        $planningTable = (new PlanningProduksi())->getTable();
                        $bagianCaseSql = "COALESCE({$planningTable}.bagian, CASE " .
                            "WHEN {$planningTable}.meja_id IS NOT NULL THEN 'assy' " .
                            "WHEN {$planningTable}.mesin_id IS NOT NULL THEN 'inject' " .
                            "ELSE 'inject' END)";
                        $q->whereRaw("{$bagianCaseSql} = ?", [$userBagian]);
                    });
                }
            }

            // Leader filter by group if they have one
            if ($user->role === 'leader' && isset($user->grup_id)) {
                $pendingQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            // Foreman hanya bisa lihat data dari grup mereka
            if ($user->role === 'foreman' && isset($user->grup_id)) {
                $pendingQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            $pending = $pendingQuery->latest()->get();

            Log::info('Pending count: ' . $pending->count());

            $pendingMapped = $pending->map(function ($item) {
                // Gunakan relasi planning langsung, bukan dari ploting
                $planning = $item->planning;
                $ploting = $item->ploting;

                // Tentukan shift dari jadwal kerja operator (prioritas utama)
                $shift = null;
                if ($ploting && $ploting->operator) {
                    $tanggalInput = $item->created_at ? $item->created_at->format('Y-m-d') : date('Y-m-d');
                    $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                        ->whereDate('tanggal', $tanggalInput)
                        ->where('status', 'aktif')
                        ->first();

                    if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                        $shift = strtolower($jadwal->shift);
                    }
                }

                // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                    $shift = strtolower($planning->shift ?? '');
                    if (!in_array($shift, ['pagi', 'siang', 'malam'])) {
                        $hour = $item->created_at->hour;
                        if ($hour >= 7 && $hour < 15) {
                            $shift = 'pagi';
                        } elseif ($hour >= 15 && $hour < 23) {
                            $shift = 'siang';
                        } else {
                            $shift = 'malam';
                        }
                    }
                }

                // Map shift ke display name
                $shiftDisplay = '';
                if ($shift === 'pagi') {
                    $shiftDisplay = 'Shift 1 (Pagi)';
                } elseif ($shift === 'siang') {
                    $shiftDisplay = 'Shift 2 (Siang)';
                } elseif ($shift === 'malam') {
                    $shiftDisplay = 'Shift 3 (Malam)';
                }

                // Ambil grup dari ploting atau dari operator
                $grupName = '-';
                if ($ploting) {
                    // Cek dari ploting->grup terlebih dahulu
                    if ($ploting->grup) {
                        $grupName = $ploting->grup->nama_grup ?? '-';
                    } elseif ($ploting->user && $ploting->user->grup) {
                        // Jika tidak ada di ploting, ambil dari user
                        $grupName = $ploting->user->grup->nama_grup ?? '-';
                    } elseif ($ploting->operator && $ploting->operator->grup) {
                        // Atau dari operator
                        $grupName = $ploting->operator->grup->nama_grup ?? '-';
                    }
                }

                // Determine bagian
                $bagian = 'inject';
                $mesinOrMeja = $planning?->mesin?->nama_mesin ?? '-';
                if ($planning) {
                    if ($planning->bagian) {
                        $bagian = strtolower($planning->bagian);
                    } elseif ($planning->meja_id) {
                        $bagian = 'assy';
                    }

                    if ($bagian === 'assy') {
                        $mesinOrMeja = $planning->meja?->nama_meja ?? '-';
                    }
                }

                // Cek apakah sudah diverifikasi oleh leader
                $isVerified = $ploting && $ploting->persiapanChecks()
                    ->where('leader_checked', true)
                    ->exists();

                return [
                    'id' => $item->id,
                    'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'operator_name' => $ploting?->user?->nama ?? ($ploting?->operator?->nama ?? 'Unknown'),
                    'grup_name' => $grupName,
                    'part_code' => $planning?->part?->no_parts ?? '-',
                    'part_name' => $planning?->part?->nama_part ?? '-',
                    'mold_no' => $planning?->moldshop?->no_mold ?? '-',
                    'mesin_name' => $mesinOrMeja,
                    'bagian' => $bagian,
                    'ok_count' => (int) ($item->ok_count ?? 0),
                    'ng_count' => (int) ($item->ng_count ?? 0),
                    'target' => (int) ($planning?->qty_planning_produksi ?? 0),
                    'shift' => $shift,
                    'shift_display' => $shiftDisplay,
                    'is_verified' => $isVerified // Flag verifikasi
                ];
            })->values();

            $approvedQuery = InputProduksi::where('status', 'approved')
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->with([
                    'ploting.user',
                    'planning.part',
                    'planning.moldshop',
                    'planning.mesin',
                    'planning.meja',
                    'user' // approver
                ]);

            // Filter approved berdasarkan role (sama seperti pending)
            if ($user->role === 'leader' && isset($user->bagian)) {
                $userBagian = strtolower($user->bagian);
                if (in_array($userBagian, ['inject', 'assy'])) {
                    $approvedQuery->whereHas('planning', function ($q) use ($userBagian) {
                        $planningTable = (new PlanningProduksi())->getTable();
                        $bagianCaseSql = "COALESCE({$planningTable}.bagian, CASE " .
                            "WHEN {$planningTable}.meja_id IS NOT NULL THEN 'assy' " .
                            "WHEN {$planningTable}.mesin_id IS NOT NULL THEN 'inject' " .
                            "ELSE 'inject' END)";
                        $q->whereRaw("{$bagianCaseSql} = ?", [$userBagian]);
                    });
                }
            }

            // Leader filter by group if they have one
            if ($user->role === 'leader' && isset($user->grup_id)) {
                $approvedQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            if ($user->role === 'foreman' && isset($user->grup_id)) {
                $approvedQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            $approved = $approvedQuery->latest('approved_at')->get()
                ->map(function ($item) {
                    $planning = $item->planning;
                    $ploting = $item->ploting;

                    // Tentukan shift dari jadwal kerja operator (prioritas utama)
                    $shift = null;
                    if ($ploting && $ploting->operator) {
                        $tanggalInput = $item->created_at ? $item->created_at->format('Y-m-d') : date('Y-m-d');
                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                            ->whereDate('tanggal', $tanggalInput)
                            ->where('status', 'aktif')
                            ->first();

                        if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                            $shift = strtolower($jadwal->shift);
                        }
                    }

                    // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                    if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                        $shift = strtolower($planning->shift ?? '');
                        if (!in_array($shift, ['pagi', 'siang', 'malam'])) {
                            $hour = $item->created_at->hour;
                            if ($hour >= 7 && $hour < 15) {
                                $shift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $shift = 'siang';
                            } else {
                                $shift = 'malam';
                            }
                        }
                    }

                    // Map shift ke display name
                    $shiftDisplay = '';
                    if ($shift === 'pagi') {
                        $shiftDisplay = 'Shift 1 (Pagi)';
                    } elseif ($shift === 'siang') {
                        $shiftDisplay = 'Shift 2 (Siang)';
                    } elseif ($shift === 'malam') {
                        $shiftDisplay = 'Shift 3 (Malam)';
                    }

                    // Ambil grup dari ploting atau dari operator
                    $grupName = '-';
                    if ($ploting) {
                        // Cek dari ploting->grup terlebih dahulu
                        if ($ploting->grup) {
                            $grupName = $ploting->grup->nama_grup ?? '-';
                        } elseif ($ploting->user && $ploting->user->grup) {
                            // Jika tidak ada di ploting, ambil dari user
                            $grupName = $ploting->user->grup->nama_grup ?? '-';
                        } elseif ($ploting->operator && $ploting->operator->grup) {
                            // Atau dari operator
                            $grupName = $ploting->operator->grup->nama_grup ?? '-';
                        }
                    }

                    // Determine bagian
                    $bagian = 'inject';
                    $mesinOrMeja = $planning?->mesin?->nama_mesin ?? '-';
                    if ($planning) {
                        if ($planning->bagian) {
                            $bagian = strtolower($planning->bagian);
                        } elseif ($planning->meja_id) {
                            $bagian = 'assy';
                        }

                        if ($bagian === 'assy') {
                            $mesinOrMeja = $planning->meja?->nama_meja ?? '-';
                        }
                    }

                    return [
                        'id' => $item->id,
                        'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : now()->toDateTimeString(),
                        'operator_name' => $ploting?->user?->nama ?? ($ploting?->operator?->nama ?? 'Unknown'),
                        'grup_name' => $grupName,
                        'part_code' => $planning?->part?->no_parts ?? '-',
                        'part_name' => $planning?->part?->nama_part ?? '-',
                        'mold_no' => $planning?->moldshop?->no_mold ?? '-',
                        'mesin_name' => $mesinOrMeja,
                        'bagian' => $bagian,
                        'ok_count' => (int) ($item->ok_count ?? 0),
                        'ng_count' => (int) ($item->ng_count ?? 0),
                        'target' => (int) ($planning?->qty_planning_produksi ?? 0),
                        'approved_by_name' => $item->user?->nama ?? '-',
                        'approved_at' => $item->approved_at ? $item->approved_at->toDateTimeString() : null,
                        'shift' => $shift,
                        'shift_display' => $shiftDisplay
                    ];
                })->values();

            $rejectedQuery = InputProduksi::where('status', 'rejected')
                ->whereBetween('rejected_at', [$startDate, $endDate])
                ->with([
                    'ploting.user.grup',
                    'ploting.operator.grup',
                    'ploting.grup',
                    'planning.part',
                    'planning.moldshop',
                    'planning.mesin',
                    'planning.meja',
                    'user' // rejector
                ]);

            // Filter rejected berdasarkan role (sama seperti pending)
            if ($user->role === 'leader' && isset($user->bagian)) {
                $userBagian = strtolower($user->bagian);
                if (in_array($userBagian, ['inject', 'assy'])) {
                    $rejectedQuery->whereHas('planning', function ($q) use ($userBagian) {
                        $planningTable = (new PlanningProduksi())->getTable();
                        $bagianCaseSql = "COALESCE({$planningTable}.bagian, CASE " .
                            "WHEN {$planningTable}.meja_id IS NOT NULL THEN 'assy' " .
                            "WHEN {$planningTable}.mesin_id IS NOT NULL THEN 'inject' " .
                            "ELSE 'inject' END)";
                        $q->whereRaw("{$bagianCaseSql} = ?", [$userBagian]);
                    });
                }
            }

            // Leader filter by group if they have one
            if ($user->role === 'leader' && isset($user->grup_id)) {
                $rejectedQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            if ($user->role === 'foreman' && isset($user->grup_id)) {
                $rejectedQuery->whereHas('ploting', function ($q) use ($user) {
                    $q->where('grup_id', $user->grup_id);
                });
            }

            $rejected = $rejectedQuery->latest('rejected_at')->get()
                ->map(function ($item) {
                    $planning = $item->planning;
                    $ploting = $item->ploting;

                    // Tentukan shift dari jadwal kerja operator (prioritas utama)
                    $shift = null;
                    if ($ploting && $ploting->operator) {
                        $tanggalInput = $item->created_at ? $item->created_at->format('Y-m-d') : date('Y-m-d');
                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                            ->whereDate('tanggal', $tanggalInput)
                            ->where('status', 'aktif')
                            ->first();

                        if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                            $shift = strtolower($jadwal->shift);
                        }
                    }

                    // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                    if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                        $shift = strtolower($planning->shift ?? '');
                        if (!in_array($shift, ['pagi', 'siang', 'malam'])) {
                            $hour = $item->created_at->hour;
                            if ($hour >= 7 && $hour < 15) {
                                $shift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $shift = 'siang';
                            } else {
                                $shift = 'malam';
                            }
                        }
                    }

                    // Map shift ke display name
                    $shiftDisplay = '';
                    if ($shift === 'pagi') {
                        $shiftDisplay = 'Shift 1 (Pagi)';
                    } elseif ($shift === 'siang') {
                        $shiftDisplay = 'Shift 2 (Siang)';
                    } elseif ($shift === 'malam') {
                        $shiftDisplay = 'Shift 3 (Malam)';
                    }

                    // Ambil grup dari ploting atau dari operator
                    $grupName = '-';
                    if ($ploting) {
                        // Cek dari ploting->grup terlebih dahulu
                        if ($ploting->grup) {
                            $grupName = $ploting->grup->nama_grup ?? '-';
                        } elseif ($ploting->user && $ploting->user->grup) {
                            // Jika tidak ada di ploting, ambil dari user
                            $grupName = $ploting->user->grup->nama_grup ?? '-';
                        } elseif ($ploting->operator && $ploting->operator->grup) {
                            // Atau dari operator
                            $grupName = $ploting->operator->grup->nama_grup ?? '-';
                        }
                    }

                    // Determine bagian
                    $bagian = 'inject';
                    $mesinOrMeja = $planning?->mesin?->nama_mesin ?? '-';
                    if ($planning) {
                        if ($planning->bagian) {
                            $bagian = strtolower($planning->bagian);
                        } elseif ($planning->meja_id) {
                            $bagian = 'assy';
                        }

                        if ($bagian === 'assy') {
                            $mesinOrMeja = $planning->meja?->nama_meja ?? '-';
                        }
                    }

                    return [
                        'id' => $item->id,
                        'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : now()->toDateTimeString(),
                        'operator_name' => $ploting?->user?->nama ?? ($ploting?->operator?->nama ?? 'Unknown'),
                        'grup_name' => $grupName,
                        'part_code' => $planning?->part?->no_parts ?? '-',
                        'part_name' => $planning?->part?->nama_part ?? '-',
                        'mold_no' => $planning?->moldshop?->no_mold ?? '-',
                        'mesin_name' => $mesinOrMeja,
                        'bagian' => $bagian,
                        'ok_count' => (int) ($item->ok_count ?? 0),
                        'ng_count' => (int) ($item->ng_count ?? 0),
                        'rejection_reason' => $item->ng_description ?? 'Tidak ada alasan',
                        'rejected_by_name' => $item->user?->nama ?? '-',
                        'rejected_at' => $item->rejected_at ? $item->rejected_at->toDateTimeString() : null,
                        'shift' => $shift,
                        'shift_display' => $shiftDisplay
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'pending' => $pendingMapped,
                    'approved' => $approved,
                    'rejected' => $rejected
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getApprovalData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve input produksi (Leader, Foreman, Kabag & Super Admin)
     */
    public function approve($id)
    {
        $user = Auth::user();

        // Cek role
        if (!in_array($user->role, ['leader', 'foreman', 'kabag', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk approve'
            ], 403);
        }

        // Leader hanya bisa approve bagian mereka
        if ($user->role === 'leader' && isset($user->bagian)) {
            $input = InputProduksi::with('planning')->findOrFail($id);
            $planning = $input->planning;

            if ($planning) {
                $resolvedBagian = $this->resolveBagian($planning);
                $userBagian = strtolower($user->bagian);

                if ($resolvedBagian !== $userBagian) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda hanya dapat approve data dari bagian ' . ucfirst($userBagian)
                    ], 403);
                }
            }
        }

        // Foreman hanya bisa approve data dari grup mereka
        if ($user->role === 'foreman' && isset($user->grup_id)) {
            $input = InputProduksi::with('ploting')->findOrFail($id);
            $ploting = $input->ploting;

            if ($ploting && $ploting->grup_id !== $user->grup_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat approve data dari grup Anda'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $input = InputProduksi::with(['ploting.operator', 'planning', 'ploting.persiapanChecks'])->findOrFail($id);

            // Cek jika sudah approved/rejected
            if ($input->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah diproses sebelumnya'
                ], 400);
            }

            // Syarat: Leader harus verifikasi persiapan terlebih dahulu
            $hasLeaderVerified = $input->ploting && $input->ploting->persiapanChecks()
                ->where('leader_checked', true)
                ->exists();

            if (!$hasLeaderVerified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus melakukan verifikasi persiapan proses produksi terlebih dahulu sebelum menyetujui data ini.'
                ], 403);
            }

            // Generate QR code tanda tangan operator
            $operatorQr = $this->generateSignatureQr($input, 'operator');

            // Generate QR code tanda tangan leader
            $leaderQr = $this->generateSignatureQr($input, 'leader', $user);

            $input->update([
                'status' => 'approved',
                'user_id' => $user->id, // simpan siapa yang approve
                'approved_at' => now(),
                'operator_signature_qr' => $operatorQr,
                'leader_signature_qr' => $leaderQr
            ]);

            // OEE Record generation dihapus - dashboard OEE menghitung real-time
            // Tidak perlu menyimpan ke tabel oee_records

            DB::commit();

            Log::info('Input approved', [
                'input_id' => $id,
                'approved_by' => $user->id,
                'planning_id' => $input->planning_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data produksi berhasil disetujui dan telah masuk ke sistem OEE'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in approve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject input produksi (Leader, Foreman, Kabag & Super Admin)
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        // Cek role
        if (!in_array($user->role, ['leader', 'foreman', 'kabag', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk reject'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $input = InputProduksi::with(['planning', 'ploting'])->findOrFail($id);

            // Leader hanya bisa reject bagian mereka
            if ($user->role === 'leader' && isset($user->bagian)) {
                $planning = $input->planning;

                if ($planning) {
                    $resolvedBagian = $this->resolveBagian($planning);
                    $userBagian = strtolower($user->bagian);

                    if ($resolvedBagian !== $userBagian) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda hanya dapat reject data dari bagian ' . ucfirst($userBagian)
                        ], 403);
                    }
                }
            }

            // Foreman hanya bisa reject data dari grup mereka
            if ($user->role === 'foreman' && isset($user->grup_id)) {
                $ploting = $input->ploting;

                if ($ploting && $ploting->grup_id !== $user->grup_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda hanya dapat reject data dari grup Anda'
                    ], 403);
                }
            }

            // Cek jika sudah approved/rejected
            if ($input->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah diproses sebelumnya'
                ], 400);
            }

            $input->update([
                'status' => 'rejected',
                'user_id' => $user->id, // simpan siapa yang reject
                'rejected_at' => now(),
                'ng_description' => $request->input('reason', 'Tidak ada alasan') // gunakan ng_description
            ]);

            DB::commit();

            Log::info('Input rejected', [
                'input_id' => $id,
                'rejected_by' => $user->id,
                'reason' => $request->input('reason')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data produksi berhasil ditolak'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in reject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail data untuk approval leader
     */
    public function getApprovalDetail($id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['leader', 'kabag', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $input = InputProduksi::with([
                'ploting.user',
                'ploting.operator',
                'ploting.persiapanChecks', // Added relationship
                'planning.part',
                'planning.moldshop',
                'planning.mesin',
                'user' // approver/rejector
            ])->findOrFail($id);

            $planning = $input->planning;
            $ploting = $input->ploting;

            // Tentukan shift dari jadwal kerja operator (prioritas utama)
            $shift = null;
            if ($ploting && $ploting->operator) {
                $tanggalInput = $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d');
                $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                    ->whereDate('tanggal', $tanggalInput)
                    ->where('status', 'aktif')
                    ->first();

                if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                    $shift = strtolower($jadwal->shift);
                }
            }

            // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
            if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                $shift = strtolower($planning->shift ?? '');
                if (!in_array($shift, ['pagi', 'siang', 'malam'])) {
                    $hour = $input->created_at->hour;
                    if ($hour >= 7 && $hour < 15) {
                        $shift = 'pagi';
                    } elseif ($hour >= 15 && $hour < 23) {
                        $shift = 'siang';
                    } else {
                        $shift = 'malam';
                    }
                }
            }

            $data = [
                'id' => $input->id,
                'ploting_id' => $ploting->id, // Needed for saving checks
                'shift' => $shift, // Added shift
                'created_at' => $input->created_at ? $input->created_at->toDateTimeString() : null,
                'submitted_at' => $input->submitted_at ? $input->submitted_at->toDateTimeString() : null,
                'status' => $input->status,
                'operator_name' => $ploting?->user?->nama ?? ($ploting?->operator?->nama ?? 'Unknown'),
                'part_code' => $planning?->part?->no_parts ?? '-',
                'part_name' => $planning?->part?->nama_part ?? '-',
                'mold_no' => $planning?->moldshop?->no_mold ?? '-',
                'mesin_name' => $planning?->mesin?->nama_mesin ?? '-',
                'customer' => $planning?->moldshop?->customer ?? null,
                'ok_count' => (int) ($input->ok_count ?? 0),
                'ng_count' => (int) ($input->ng_count ?? 0),
                'ng_material' => (int) ($input->ng_material ?? 0),
                'ng_process' => (int) ($input->ng_process ?? 0),
                'ng_machine' => (int) ($input->ng_machine ?? 0),
                'ng_operator' => (int) ($input->ng_operator ?? 0),
                'ng_other' => (int) ($input->ng_other ?? 0),
                'target' => (int) ($planning?->qty_planning_produksi ?? 0),
                'approved_by_name' => $input->status === 'approved' ? ($input->user?->nama ?? '-') : null,
                'approved_at' => $input->approved_at ? $input->approved_at->toDateTimeString() : null,
                'rejected_by_name' => $input->status === 'rejected' ? ($input->user?->nama ?? '-') : null,
                'rejected_at' => $input->rejected_at ? $input->rejected_at->toDateTimeString() : null,
                'rejection_reason' => $input->status === 'rejected' ? ($input->ng_description ?? 'Tidak ada alasan') : null,
                'persiapan_checks' => $ploting ? $ploting->persiapanChecks : [], // Return checks
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getApprovalDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method generateOeeRecord(), createOeeRecord(), updateOeeRecord(), dan calculateRunTime() dihapus
    // karena tabel oee_records tidak digunakan. Dashboard OEE menghitung data secara real-time.

    // ===================== CRUD METHODS =====================

    /**
     * Show detail input produksi (for modal)
     */
    public function show(InputProduksi $input)
    {
        $user = Auth::user();

        // Izinkan super_admin, kabag, foreman, dan leader untuk melihat detail
        if (!in_array($user->role, ['super_admin', 'kabag', 'foreman', 'leader'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Leader hanya bisa lihat detail dari bagian mereka
        if ($user->role === 'leader' && isset($user->bagian)) {
            $planning = $input->planning;
            if ($planning) {
                $resolvedBagian = $this->resolveBagian($planning);
                $userBagian = strtolower($user->bagian);

                if ($resolvedBagian !== $userBagian) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized - Anda hanya dapat melihat data dari bagian ' . ucfirst($userBagian)
                    ], 403);
                }
            }
        }

        // Foreman hanya bisa lihat detail dari grup mereka
        if ($user->role === 'foreman' && isset($user->grup_id)) {
            $ploting = $input->ploting;
            if ($ploting && $ploting->grup_id !== $user->grup_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Anda hanya dapat melihat data dari grup Anda'
                ], 403);
            }
        }

        // Load all necessary relationships
        $input->load([
            'planning.part',
            'planning.mesin',
            'planning.moldshop',
            'planning.plotings.user',
            'ploting.user',
            'user' // approver/rejector
        ]);

        // Calculate statistics
        $total = ($input->ok_count ?? 0) + ($input->ng_count ?? 0);
        $target = $input->planning?->qty_planning_produksi ?? 0;
        $gap = max(0, $target - $total);
        $percentage = $target > 0 ? min(100, round(($total / $target) * 100, 1)) : 0;
        $efficiency = $total > 0 ? round((($input->ok_count ?? 0) / $total) * 100, 1) : 100;

        // Return JSON for modal
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $input->id,
                'created_at' => $input->created_at ? $input->created_at->format('d/m/Y H:i:s') : '-',
                'submitted_at' => $input->submitted_at ? $input->submitted_at->format('d/m/Y H:i:s') : null,
                'approved_at' => $input->approved_at ? $input->approved_at->format('d/m/Y H:i:s') : null,
                'rejected_at' => $input->rejected_at ? $input->rejected_at->format('d/m/Y H:i:s') : null,
                'status' => $input->status,
                'part_code' => $input->planning?->part?->no_parts ?? 'N/A',
                'part_name' => $input->planning?->part?->nama_part ?? 'N/A',
                'mesin' => $input->planning?->mesin?->nama_mesin ?? 'N/A',
                'mold_name' => $input->planning?->moldshop?->nama_mold ?? 'N/A',
                'customer' => $input->planning?->moldshop?->customer ?? 'N/A',
                'target' => $target,
                'ok_count' => $input->ok_count ?? 0,
                'ng_count' => $input->ng_count ?? 0,
                'ng_material' => $input->ng_material ?? 0,
                'ng_process' => $input->ng_process ?? 0,
                'ng_machine' => $input->ng_machine ?? 0,
                'ng_operator' => $input->ng_operator ?? 0,
                'ng_other' => $input->ng_other ?? 0,
                'ng_description' => $input->ng_description ?? null,
                'operator_name' => $input->ploting?->user?->nama ?? ($input->ploting?->operator?->nama ?? 'N/A'),
                'approved_by' => $input->user?->nama ?? null,
                'total' => $total,
                'gap' => $gap,
                'percentage' => $percentage,
                'efficiency' => $efficiency,
            ]
        ]);
    }

    public function edit(InputProduksi $input)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'kabag'])) {
            abort(403, 'Unauthorized');
        }

        return view('input-produksi.edit', compact('input'));
    }

    public function update(Request $request, InputProduksi $input)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'kabag'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'ok_count' => 'required|integer|min:0',
            'ng_count' => 'required|integer|min:0',
        ]);

        $input->update($validated);

        return redirect()->route('input-produksi.index')->with('success', 'Data produksi berhasil diperbarui');
    }

    /**
     * Generate QR code untuk tanda tangan operator atau leader
     */
    private function generateSignatureQr(InputProduksi $input, $type = 'operator', $leader = null)
    {
        try {
            // Load relasi yang diperlukan
            $input->load(['ploting.operator', 'planning']);

            $user = null;
            $qrData = [];

            if ($type === 'operator') {
                // Ambil operator dari ploting
                $operator = $input->ploting->operator ?? null;
                if (!$operator) {
                    Log::warning('Operator not found for input produksi', ['input_id' => $input->id]);
                    return null;
                }
                $user = $operator;

                $qrData = [
                    'type' => 'OPERATOR_SIGNATURE',
                    'input_produksi_id' => $input->id,
                    'operator_id' => $operator->id,
                    'operator_nama' => $operator->nama,
                    'operator_nik' => $operator->nik,
                    'planning_id' => $input->planning_id,
                    'approved_at' => now()->toISOString(),
                    'signature_type' => 'operator'
                ];
            } else {
                // Leader yang approve
                if (!$leader) {
                    $leader = Auth::user();
                }
                $user = $leader;

                $qrData = [
                    'type' => 'LEADER_SIGNATURE',
                    'input_produksi_id' => $input->id,
                    'leader_id' => $leader->id,
                    'leader_nama' => $leader->nama,
                    'leader_nik' => $leader->nik,
                    'planning_id' => $input->planning_id,
                    'approved_at' => now()->toISOString(),
                    'signature_type' => 'leader'
                ];
            }

            // Convert to JSON string
            $qrContent = json_encode($qrData, JSON_UNESCAPED_UNICODE);

            // Generate QR code
            $writer = new PngWriter();
            $builder = new Builder($writer);

            $result = $builder->build(
                data: $qrContent,
                size: 200,
                margin: 5
            );

            // Save QR code to storage
            $directory = 'public/signature_qr';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            $filename = 'signature_qr/' . $input->id . '_' . $type . '_' . time() . '.png';
            $path = 'public/' . $filename;

            // Get binary data
            $qrDataBinary = $result->getString();
            Storage::put($path, $qrDataBinary);

            Log::info('QR code signature generated', [
                'input_id' => $input->id,
                'type' => $type,
                'user' => $user->nama,
                'filename' => $filename
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to generate signature QR code: ' . $e->getMessage(), [
                'input_id' => $input->id,
                'type' => $type,
                'error' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function destroy(InputProduksi $input)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $input->delete();

        return redirect()->route('input-produksi.index')->with('success', 'Data produksi berhasil dihapus');
    }
    public function getPersiapan($plotingId)
    {
        $checks = PersiapanProduksiCheck::where('ploting_id', $plotingId)->get();
        return response()->json($checks);
    }

    public function storePersiapan(Request $request, $plotingId)
    {
        Log::info('storePersiapan called', ['plotingId' => $plotingId, 'data' => $request->all()]);

        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.item_name' => 'required|string',
                'items.*.operator_checked' => 'boolean',
                'items.*.leader_checked' => 'boolean',
                'items.*.keterangan' => 'nullable|string',
            ]);

            foreach ($request->items as $item) {
                PersiapanProduksiCheck::updateOrCreate(
                    [
                        'ploting_id' => $plotingId,
                        'item_name' => $item['item_name']
                    ],
                    [
                        'operator_checked' => $item['operator_checked'] ?? false,
                        'leader_checked' => $item['leader_checked'] ?? false,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]
                );
            }

            Log::info('storePersiapan success');
            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in storePersiapan', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in storePersiapan: ' . $e->getMessage());
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}
