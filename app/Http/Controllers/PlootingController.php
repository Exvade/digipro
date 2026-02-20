<?php

namespace App\Http\Controllers;

use App\Models\PlanningProduksi;
use App\Models\Ploting;
use App\Models\Grup;
use App\Models\User;
use App\Models\JadwalKerja;
use App\Models\InputProduksi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlootingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user yang sedang login
        $user = auth()->user();
        
        // Cek apakah user adalah leader dengan bagian inject
        $isLeaderInject = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject');
        $isLeaderAssy = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy');
        
        // Ambil filter tanggal (default: hari ini)
        // Untuk ploting H-1, biasanya menggunakan tanggal besok
        $filterTanggal = $request->get('tanggal', date('Y-m-d'));

        // Pisahkan planning berdasarkan bagian (inject dan assy)
        $planningsInject = PlanningProduksi::with([
            'part',
            'mesin',
            'moldshop',
            'jam',
            'plotings.grup',
            'plotings.operator.jadwalKerjas' => function($query) use ($filterTanggal) {
                $query->whereDate('tanggal', $filterTanggal)
                      ->where('status', 'aktif')
                      ->latest('tanggal');
            },
        ])
        ->whereDate('tanggal', $filterTanggal)
        ->where('status_approval', 'approved') // Hanya planning yang sudah di-approve
        ->where('bagian', 'inject')
        ->get();

        // Jika leader inject, jangan tampilkan data assy
        // Jika leader assy, jangan tampilkan data inject
        if ($isLeaderInject) {
            $planningsAssy = collect([]); // Empty collection
        } elseif ($isLeaderAssy) {
            $planningsInject = collect([]); // Empty collection untuk leader assy
            $planningsAssy = PlanningProduksi::with([
                'part',
                'meja',
                'jam',
                'plotings.grup',
                'plotings.operator.jadwalKerjas' => function($query) use ($filterTanggal) {
                    $query->whereDate('tanggal', $filterTanggal)
                          ->where('status', 'aktif')
                          ->latest('tanggal');
                },
            ])
            ->whereDate('tanggal', $filterTanggal)
            ->where('status_approval', 'approved')
            ->where('bagian', 'assy')
            ->get();
        } else {
            $planningsAssy = PlanningProduksi::with([
                'part',
                'meja',
                'jam',
                'plotings.grup',
                'plotings.operator.jadwalKerjas' => function($query) use ($filterTanggal) {
                    $query->whereDate('tanggal', $filterTanggal)
                          ->where('status', 'aktif')
                          ->latest('tanggal');
                },
            ])
            ->whereDate('tanggal', $filterTanggal)
            ->where('status_approval', 'approved')
            ->where('bagian', 'assy')
            ->get();
        }

        $grups = Grup::all();

        $usersInject = User::where('role', 'operator')
            ->where('bagian', 'inject')
            ->get();

        $usersAssy = User::where('role', 'operator')
            ->where('bagian', 'assy')
            ->get();

        return view('ploting.index', compact('planningsInject', 'planningsAssy', 'grups', 'usersInject', 'usersAssy', 'filterTanggal', 'isLeaderInject', 'isLeaderAssy'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'planning_id' => 'required|exists:planning_produksi,id',
            'grup_id'     => 'required|exists:grups,id',
            'user_id'     => 'required|exists:users,id',
        ]);

        // Pastikan planning sudah di-approve
        $planning = PlanningProduksi::findOrFail($request->planning_id);
        if ($planning->status_approval !== 'approved') {
            return back()->withErrors('Planning harus di-approve terlebih dahulu sebelum bisa di-ploting.');
        }

        $exists = Ploting::where('planning_id', $request->planning_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return back()->withErrors('Operator sudah di-assign ke planning ini.');
        }

        Ploting::create([
            'planning_id' => $request->planning_id,
            'grup_id'     => $request->grup_id,
            'user_id'     => $request->user_id,
            'status'      => 'waiting',
        ]);

        return redirect()->back()->with('success', 'Operator berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'grup_id' => 'required|exists:grups,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $ploting = Ploting::findOrFail($id);

        if (!in_array($ploting->status, [null, 'waiting'])) {
            return back()->withErrors('Tidak bisa mengubah operator setelah produksi dimulai atau selesai.');
        }

        $duplicate = Ploting::where('planning_id', $ploting->planning_id)
            ->where('user_id', $request->user_id)
            ->where('id', '!=', $ploting->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors('Operator ini sudah terdaftar di planning yang sama.');
        }

        $ploting->update([
            'grup_id' => $request->grup_id,
            'user_id' => $request->user_id,
        ]);

        return back()->with('success', 'Plooting berhasil diperbarui!');
    }

    public function start($id)
    {
        $ploting = Ploting::with('planning', 'operator')->findOrFail($id);

        if ($ploting->status === 'on_progress') {
            return back()->withErrors('Produksi sudah berjalan.');
        }

        // Cek apakah shift masih aktif sebelum start
        $shiftStartTime = null;
        $shiftEndTime = null;
        $canStart = true;
        $errorMessage = '';
        
        if ($ploting->operator) {
            $planning = $ploting->planning;
            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : date('Y-m-d');
            
            $jadwal = JadwalKerja::where('user_id', $ploting->operator->id)
                ->whereDate('tanggal', $tanggalPlotting)
                ->where('status', 'aktif')
                ->first();
            
            // Cek apakah operator memiliki jadwal kerja aktif
            if (!$jadwal) {
                $canStart = false;
                $errorMessage = 'Operator tidak memiliki jadwal kerja aktif pada tanggal ' . Carbon::parse($tanggalPlotting)->format('d-m-Y') . '. Silakan hubungi admin untuk mengatur jadwal kerja.';
            } elseif ($jadwal->shift === 'off') {
                // Cek apakah jadwal adalah libur
                $canStart = false;
                $errorMessage = 'Operator sedang libur (shift off) pada tanggal ini. Tidak dapat memulai produksi.';
            } elseif ($jadwal->jam_masuk && $jadwal->jam_keluar) {
                try {
                    $jamKeluar = Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                } catch (\Exception $e) {
                    $jamKeluar = Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                }
                
                // Hitung shift start time dan end time
                $shiftStartTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_masuk);
                
                // Jika shift malam (jam keluar < jam masuk), tambahkan 1 hari untuk end time
                if ($jamKeluar->lessThan($jamMasuk)) {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                    // Untuk shift malam, earliest start time bisa jadi hari sebelumnya
                    // Contoh: Shift malam mulai 23:00, earliest start = 22:00 (hari yang sama)
                    // Tapi jika sekarang masih pagi/siang hari sebelumnya, tidak bisa start
                } else {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                }
                
                $now = now();
                
                // Izinkan start 1 jam sebelum shift dimulai
                $earliestStartTime = $shiftStartTime->copy()->subHour();
                
                // Untuk shift malam yang melintasi hari, pastikan kita membandingkan dengan benar
                // Jika shift malam (jam keluar < jam masuk), earliest start time tetap di hari yang sama dengan shift start
                // Contoh: Shift mulai 23:00 hari ini → earliest start = 22:00 hari ini
                
                // Jika waktu sekarang lebih awal dari 1 jam sebelum shift dimulai, tidak bisa start
                if ($now->lessThan($earliestStartTime)) {
                    $canStart = false;
                    $errorMessage = 'Tidak dapat memulai produksi. Shift dimulai pada ' . $shiftStartTime->format('d-m-Y H:i') . '. Anda dapat mulai 1 jam sebelumnya (mulai ' . $earliestStartTime->format('d-m-Y H:i') . ').';
                }
                
                // Jika shift sudah berakhir lebih dari 1 jam, tidak bisa start
                // (beri toleransi 1 jam untuk kasus edge)
                $shiftEndTimeWithTolerance = $shiftEndTime->copy()->addHour();
                if ($now->greaterThan($shiftEndTimeWithTolerance)) {
                    $canStart = false;
                    $errorMessage = 'Tidak dapat memulai produksi karena shift sudah berakhir lebih dari 1 jam.';
                }
            } else {
                // Jadwal ada tapi tidak memiliki jam masuk/keluar
                $canStart = false;
                $errorMessage = 'Jadwal kerja tidak lengkap (tidak ada jam masuk/keluar). Silakan hubungi admin.';
            }
        }

        if (!$canStart) {
            return back()->withErrors($errorMessage ?: 'Tidak dapat memulai produksi karena shift sudah berakhir.');
        }

        $ploting->update([
            'status' => 'on_progress',
            'start_time' => now(),
            'end_time' => null,
            'run_time' => null,
        ]);

        return redirect()->back()->with('success', 'Produksi dimulai!');
    }

    public function finish($id)
    {
        $ploting = Ploting::with('planning', 'operator')->findOrFail($id);

        // Cek apakah sudah selesai atau expired
        if (in_array($ploting->status, ['done', 'expired'])) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produksi sudah selesai.',
                    'status' => $ploting->status
                ]);
            }
            return back()->with('info', 'Produksi sudah selesai.');
        }

        if ($ploting->status !== 'on_progress' || !$ploting->start_time) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produksi belum dimulai atau sudah selesai.'
                ], 422);
            }
            return back()->withErrors('Produksi belum dimulai atau sudah selesai.');
        }

        // Cek shift end time untuk menentukan waktu akhir
        $shiftEndTime = null;
        $end = now();
        
        if ($ploting->operator) {
            $planning = $ploting->planning;
            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : date('Y-m-d');
            
            $jadwal = JadwalKerja::where('user_id', $ploting->operator->id)
                ->whereDate('tanggal', $tanggalPlotting)
                ->where('status', 'aktif')
                ->first();
            
            if ($jadwal && $jadwal->jam_keluar) {
                try {
                    $jamKeluar = Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                } catch (\Exception $e) {
                    $jamKeluar = Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                }
                
                // Jika shift malam (jam keluar < jam masuk), tambahkan 1 hari
                if ($jamKeluar->lessThan($jamMasuk)) {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                } else {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                }
                
                // Jika shift sudah berakhir, gunakan shift end time
                if (now()->greaterThan($shiftEndTime)) {
                    $end = $shiftEndTime;
                }
            }
        }

        $start = Carbon::parse($ploting->start_time);
        
        // Pastikan perhitungan run time akurat
        $runTimeSeconds = $end->diffInSeconds($start);
        
        // Validasi run time minimal 1 detik
        if ($runTimeSeconds < 1) {
            $runTimeSeconds = 1;
        }

        $ploting->update([
            'status' => 'done',
            'end_time' => $end,
            'run_time' => $runTimeSeconds,
        ]);

        // Format durasi untuk pesan sukses
        $hours = floor($runTimeSeconds / 3600);
        $minutes = floor(($runTimeSeconds % 3600) / 60);
        $seconds = $runTimeSeconds % 60;
        $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Produksi selesai! Durasi: {$duration}",
                'duration' => $duration,
                'run_time' => $runTimeSeconds
            ]);
        }

        return redirect()->back()->with('success', "Produksi selesai! Durasi: {$duration}");
    }

    /**
     * Cek apakah target shift sudah tercapai untuk ploting tertentu
     */
    private function checkShiftTargetReached($ploting)
    {
        if (!$ploting->operator || !$ploting->planning) {
            return false;
        }

        $planning = $ploting->planning;
        $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : date('Y-m-d');
        
        // Ambil jadwal kerja operator untuk menentukan shift
        $jadwal = JadwalKerja::where('user_id', $ploting->operator->id)
            ->whereDate('tanggal', $tanggalPlotting)
            ->where('status', 'aktif')
            ->first();
        
        if (!$jadwal) {
            return false;
        }

        // Tentukan shift dari jadwal kerja
        $shift = $jadwal->shift;
        if ($shift === 'off') {
            return false;
        }

        // Jika shift tidak ada di jadwal, tentukan berdasarkan jam masuk
        if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
            try {
                $jamMasuk = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
            } catch (\Exception $e) {
                $jamMasuk = Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
            }
            $hour = (int)$jamMasuk->format('H');
            
            if ($hour >= 7 && $hour < 15) {
                $shift = 'pagi';
            } elseif ($hour >= 15 && $hour < 23) {
                $shift = 'siang';
            } else {
                $shift = 'malam';
            }
        }

        // Ambil target shift
        $targetShift = 0;
        switch ($shift) {
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
        $totalInputShift = $this->getTotalInputByShift($planning->id, $shift);

        // Jika total input sudah mencapai atau melebihi target
        return $totalInputShift >= $targetShift;
    }

    /**
     * Hitung total input per shift berdasarkan waktu updated_at
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
            $totalInput = $query->whereDate('updated_at', $today)
                  ->whereTime('updated_at', '>=', '07:00:00')
                  ->whereTime('updated_at', '<', '15:00:00')
                  ->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        } elseif ($shift === 'siang') {
            $totalInput = $query->whereDate('updated_at', $today)
                  ->whereTime('updated_at', '>=', '15:00:00')
                  ->whereTime('updated_at', '<', '23:00:00')
                  ->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        } elseif ($shift === 'malam') {
            $totalInput = $query->where(function($q) use ($today, $tomorrow) {
                $q->where(function($subQ) use ($today) {
                    $subQ->whereDate('updated_at', $today)
                         ->whereTime('updated_at', '>=', '23:00:00');
                })->orWhere(function($subQ) use ($tomorrow) {
                    $subQ->whereDate('updated_at', $tomorrow)
                         ->whereTime('updated_at', '<', '07:00:00');
                });
            })->sum(DB::raw('ok_count + ng_count'));
            return $totalInput ?? 0;
        }
        
        return 0;
    }

    public function runtime($id)
    {
        $ploting = Ploting::with('planning', 'operator')->findOrFail($id);

        if ($ploting->status !== 'on_progress' || !$ploting->start_time) {
            return response()->json(['message' => 'Produksi belum dimulai.'], 422);
        }

        // Cek apakah target shift sudah tercapai
        $targetReached = $this->checkShiftTargetReached($ploting);
        
        // Cek apakah shift sudah berakhir
        $shiftExpired = false;
        $shiftEndTime = null;
        
        if ($ploting->operator) {
            $planning = $ploting->planning;
            $tanggalPlotting = $planning->tanggal ? $planning->tanggal->format('Y-m-d') : date('Y-m-d');
            
            $jadwal = JadwalKerja::where('user_id', $ploting->operator->id)
                ->whereDate('tanggal', $tanggalPlotting)
                ->where('status', 'aktif')
                ->first();
            
            if ($jadwal && $jadwal->jam_keluar) {
                try {
                    $jamKeluar = Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                } catch (\Exception $e) {
                    $jamKeluar = Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                    $jamMasuk = Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                }
                
                // Jika shift malam (jam keluar < jam masuk), tambahkan 1 hari
                if ($jamKeluar->lessThan($jamMasuk)) {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar)->addDay();
                } else {
                    $shiftEndTime = Carbon::parse($tanggalPlotting)->setTimeFromTimeString($jadwal->jam_keluar);
                }
                
                // Cek apakah shift sudah berakhir
                if (now()->greaterThan($shiftEndTime)) {
                    $shiftExpired = true;
                }
            }
        }

        $now = now();
        $start = Carbon::parse($ploting->start_time);
        
        // Jika target shift sudah tercapai, auto-finish ploting
        if ($targetReached && $ploting->status === 'on_progress') {
            $runTimeSeconds = max(1, $now->diffInSeconds($start));
            
            $ploting->update([
                'status' => 'done',
                'end_time' => $now,
                'run_time' => $runTimeSeconds,
            ]);
            
            return response()->json([
                'message' => 'Target shift sudah tercapai, ploting otomatis selesai',
                'run_time' => $runTimeSeconds,
                'target_reached' => true,
                'shift_expired' => false,
            ]);
        }
        
        // Jika shift sudah berakhir DAN start_time dilakukan SEBELUM shift berakhir, 
        // maka auto-finish dengan menggunakan shift end time sebagai waktu akhir
        if ($shiftExpired && $shiftEndTime && $start->lessThan($shiftEndTime)) {
            $runTimeSeconds = max(1, $shiftEndTime->diffInSeconds($start));
            
            // Otomatis finish ploting ketika shift berakhir (hanya jika start sebelum shift berakhir)
            if ($ploting->status === 'on_progress') {
                $ploting->update([
                    'status' => 'done',
                    'end_time' => $shiftEndTime,
                    'run_time' => $runTimeSeconds,
                ]);
            }
        } else {
            // Jika start_time dilakukan setelah shift berakhir, tetap hitung runtime normal
            // atau jika shift belum berakhir, hitung runtime sampai sekarang
            $runTimeSeconds = max(1, $now->diffInSeconds($start));
            
            $ploting->update([
                'run_time' => $runTimeSeconds,
            ]);
        }

        return response()->json([
            'message' => $shiftExpired ? 'Shift sudah berakhir' : 'Run time updated',
            'run_time' => $runTimeSeconds,
            'shift_expired' => $shiftExpired,
            'target_reached' => $targetReached,
        ]);
    }

    public function operatorsByGroup(Request $request)
    {
        $grupId = $request->query('grup_id');
        $tanggal = $request->query('tanggal') ?: date('Y-m-d');
        $filterShift = $request->query('shift'); // Filter shift tambahan
        $bagian = $request->query('bagian') ?: 'inject'; // Tambahan filter bagian

        if (!$grupId) {
            return response()->json([]);
        }

        // Ambil informasi grup termasuk shift
        $grup = Grup::find($grupId);
        $grupShift = $grup ? $grup->shift : null;

        // Ambil semua operator di grup berdasarkan bagian
        $users = User::where('role', 'operator')
            ->where('bagian', $bagian)
            ->where('grup_id', $grupId)
            ->get(['id', 'nama', 'nik']);

        // Jika grup memiliki shift (bukan 'all'), gunakan shift grup
        // Jika grup shift adalah 'all', ambil shift dari jadwal kerja operator
        $result = $users->map(function($u) use ($tanggal, $grupShift) {
            $shift = null;
            $jadwal = null;
            
            // Jika grup memiliki shift spesifik (bukan 'all'), gunakan shift grup
            if ($grupShift && $grupShift !== 'all') {
                $shift = $grupShift;
            } else {
                // Jika grup shift adalah 'all', ambil shift dari jadwal kerja operator
                $jadwal = JadwalKerja::where('user_id', $u->id)
                    ->whereDate('tanggal', $tanggal)
                    ->where('status', 'aktif')
                    ->first();
                
                if ($jadwal) {
                    // Gunakan shift langsung dari jadwal kerja
                    $shift = $jadwal->shift;
                    
                    // Jika shift tidak ada di jadwal, tentukan berdasarkan jam masuk
                    if (!$shift || $shift === 'off') {
                        $jamMasuk = $jadwal->jam_masuk;
                        if (is_string($jamMasuk)) {
                            try {
                                $jam = \Carbon\Carbon::createFromFormat('H:i:s', $jamMasuk);
                            } catch (\Exception $e) {
                                $jam = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk);
                            }
                            $hour = (int)$jam->format('H');
                            
                            if ($hour >= 7 && $hour < 15) {
                                $shift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $shift = 'siang';
                            } else {
                                $shift = 'malam';
                            }
                        }
                    }
                }
            }
            
            return [
                'id' => $u->id,
                'nama' => $u->nama,
                'nik' => $u->nik,
                'shift' => $shift,
                'has_jadwal' => $jadwal ? true : false,
            ];
        });

        // Filter berdasarkan shift jika ada filter shift
        if ($filterShift && $filterShift !== '') {
            $result = $result->filter(function($item) use ($filterShift) {
                return $item['shift'] === $filterShift;
            });
        }

        return response()->json([
            'data' => $result->values(),
            'grup_shift' => $grupShift,
            'message' => $result->count() > 0 
                ? "Ditemukan {$result->count()} operator di grup ini" . ($filterShift ? " untuk shift " . ucfirst($filterShift) : '')
                : "Tidak ada operator di grup ini" . ($filterShift ? " untuk shift " . ucfirst($filterShift) : '')
        ]);
    }
}
