<?php

namespace App\Http\Controllers;

use App\Models\InputProduksi;
use App\Models\ProduksiPerJam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class LaporanProduksiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Query utama dengan relasi lengkap - HANYA data yang sudah approved
        $query = InputProduksi::with([
            'planning.mesin',
            'planning.meja',
            'planning.moldshop',
            'planning.part',
            'planning.plotings.operator',
            'ploting.operator', // Operator dari ploting
            'ploting.grup', // Grup dari ploting
            'approver', // Leader yang approve
        ])->where('status', 'approved'); // ✅ Hanya tampilkan data yang sudah approved

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [
                $request->tanggal_awal . ' 00:00:00',
                $request->tanggal_akhir . ' 23:59:59'
            ]);
        }

        // Filter berdasarkan grup
        if ($request->filled('grup_filter')) {
            $query->whereHas('ploting.grup', function ($q) use ($request) {
                $q->where('id', $request->grup_filter);
            });
        }

        // Filter berdasarkan mesin
        if ($request->filled('mesin_filter')) {
            $query->whereHas('planning.mesin', function ($q) use ($request) {
                $q->where('id', $request->mesin_filter);
            });
        }

        // Leader filter by group if they have one
        if ($user->role === 'leader' && isset($user->grup_id)) {
            $query->whereHas('ploting', function ($q) use ($user) {
                $q->where('grup_id', $user->grup_id);
            });
        }

        // Leader filter by bagian (inject/assy)
        if ($user->role === 'leader' && isset($user->bagian)) {
            $userBagian = strtolower($user->bagian);
            if ($userBagian === 'inject') {
                // For inject, filter by mesin_id (not null)
                $query->whereHas('planning', function ($q) {
                    $q->whereNotNull('mesin_id');
                });
            } elseif ($userBagian === 'assy') {
                // For assy, filter by meja_id (not null)
                $query->whereHas('planning', function ($q) {
                    $q->whereNotNull('meja_id');
                });
            }
        }

        // Foreman filter by group if they have one
        if ($user->role === 'foreman' && isset($user->grup_id)) {
            $query->whereHas('ploting', function ($q) use ($user) {
                $q->where('grup_id', $user->grup_id);
            });
        }

        // Eksekusi query
        $dataProduksi = $query->latest()->get();

        // Ambil data untuk filter dropdown
        $grupsQuery = \App\Models\Grup::orderBy('nama_grup');

        // Filter dropdown grup untuk leader/foreman
        if (($user->role === 'leader' || $user->role === 'foreman') && isset($user->grup_id)) {
            $grupsQuery->where('id', $user->grup_id);
        }

        $grups = $grupsQuery->get();
        $mesins = \App\Models\Mesin::orderBy('nama_mesin')->get();

        return view('laporan-produksi.index', compact('dataProduksi', 'grups', 'mesins'));
    }

    public function cetak(Request $request)
    {
        // Increase memory and execution time for batch PDF generation
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '600');

        try {
            // Query utama dengan relasi lengkap - HANYA data yang sudah approved
            $query = InputProduksi::with([
                'planning.mesin',
                'planning.moldshop',
                'planning.part',
                'planning.plotings.operator',
                'ploting.operator',
                'approver',
            ])->where('status', 'approved'); // ✅ Hanya tampilkan data yang sudah approved

            // Filter tanggal HANYA jika KEDUA parameter ada
            if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
                $query->whereBetween('created_at', [
                    $request->tanggal_awal . ' 00:00:00',
                    $request->tanggal_akhir . ' 23:59:59'
                ]);
            }

            // Filter berdasarkan grup
            if ($request->filled('grup_filter')) {
                $query->whereHas('ploting.grup', function ($q) use ($request) {
                    $q->where('id', $request->grup_filter);
                });
            }

            // Filter berdasarkan mesin
            if ($request->filled('mesin_filter')) {
                $query->whereHas('planning.mesin', function ($q) use ($request) {
                    $q->where('id', $request->mesin_filter);
                });
            }

            $dataProduksi = $query->with([
                'planning.plotings.grup.leader',
                'ploting.grup.leader',
                'approver',
                'planning.part',
                'planning.mesin',
                'planning.meja',
                'ngPartTypes'
            ])->latest()
                ->limit(30) // Reduced limit further for stability
                ->get();

            // Debug: Check if we have data
            Log::info('Laporan Produksi PDF - Data count: ' . $dataProduksi->count());

            // Batch fetch all approved inputs for all plannings in this batch
            $planningIds = $dataProduksi->pluck('planning_id')->unique()->toArray();
            $allInputsGrouped = InputProduksi::whereIn('planning_id', $planningIds)
                ->where('status', 'approved')
                ->with(['ploting.operator', 'ploting.grup'])
                ->get()
                ->groupBy('planning_id');

            // Batch fetch JadwalKerja for all operators and dates in allInputs
            $allOperatorsForJadwal = $allInputsGrouped->flatten()->pluck('ploting.operator.id')->unique()->filter()->toArray();
            $allDatesForJadwal = $allInputsGrouped->flatten()->map(fn($input) => $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d'))->unique()->toArray();

            $jadwalKerjasGrouped = \App\Models\JadwalKerja::whereIn('user_id', $allOperatorsForJadwal)
                ->whereIn(DB::raw('DATE(tanggal)'), $allDatesForJadwal)
                ->where('status', 'aktif')
                ->get()
                ->groupBy(['user_id', function ($item) {
                    return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
                }]);

            // Prepare all data for each item
            $allData = [];
            foreach ($dataProduksi as $data) {
                $planning = $data->planning;

                // 1. Prepare Operators and Leaders by Shift
                $operatorsByShift = [
                    'shift_1' => [],
                    'shift_2' => [],
                    'shift_3' => []
                ];
                $operatorsQrByShift = [
                    'shift_1' => [],
                    'shift_2' => [],
                    'shift_3' => []
                ];

                // Ambil semua input produksi yang sudah approved untuk planning ini dari data yang sudah di-batch
                $allInputs = $allInputsGrouped->get($planning->id) ?? collect();

                // Track shift inputs for leaders
                $shiftInputs = [
                    'shift_1' => null,
                    'shift_2' => null,
                    'shift_3' => null
                ];

                foreach ($allInputs as $input) {
                    $ploting = $input->ploting;
                    if (!$ploting || !$ploting->operator) continue;

                    // Tentukan shift dari jadwal kerja operator (prioritas utama)
                    $inputShift = null;
                    $tanggalInput = $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d');

                    $jadwal = $jadwalKerjasGrouped->get($ploting->operator->id)?->get($tanggalInput)?->first();

                    if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                        $inputShift = strtolower($jadwal->shift);
                    }

                    // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                    if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                        $inputShift = $this->normalizeShift($planning->shift ?? null);
                        if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                            $hour = $input->created_at->hour;
                            if ($hour >= 7 && $hour < 15) {
                                $inputShift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $inputShift = 'siang';
                            } else {
                                $inputShift = 'malam';
                            }
                        }
                    }

                    // Map shift ke shift key
                    $shiftKey = null;
                    if ($inputShift === 'pagi') $shiftKey = 'shift_1';
                    elseif ($inputShift === 'siang') $shiftKey = 'shift_2';
                    elseif ($inputShift === 'malam') $shiftKey = 'shift_3';

                    if ($shiftKey) {
                        // Simpan input untuk shift leader
                        if (!$shiftInputs[$shiftKey]) {
                            $shiftInputs[$shiftKey] = $input;
                        }

                        if ($ploting->operator) {
                            $operatorExists = false;
                            foreach ($operatorsByShift[$shiftKey] as $existingOperator) {
                                if ($existingOperator['id'] === $ploting->operator->id) {
                                    $operatorExists = true;
                                    break;
                                }
                            }

                            if (!$operatorExists) {
                                $operatorQrPath = null;
                                if (!empty($input->operator_signature_qr)) {
                                    $operatorQrPath = $this->getQrCodePath($input->operator_signature_qr);
                                } else {
                                    $operatorQrPath = $this->getTtdQrCodePath($input->id, 'operator', $ploting->operator);
                                }

                                $operatorsByShift[$shiftKey][] = [
                                    'id' => $ploting->operator->id,
                                    'nama' => $ploting->operator->nama ?? '-',
                                    'nik' => $ploting->operator->nik ?? '-'
                                ];
                                $operatorsQrByShift[$shiftKey][] = $operatorQrPath;
                            }
                        }
                    }
                }

                // Get leaders per shift
                $leadersByShift = [
                    'shift_1' => null,
                    'shift_2' => null,
                    'shift_3' => null
                ];
                $leadersQrByShift = [
                    'shift_1' => null,
                    'shift_2' => null,
                    'shift_3' => null
                ];

                foreach (['shift_1', 'shift_2', 'shift_3'] as $shiftKey) {
                    $shiftInput = $shiftInputs[$shiftKey];

                    if ($shiftInput) {
                        $leader = $this->getLeaderByShiftAndGrup($shiftInput);
                        if ($leader) {
                            $leadersByShift[$shiftKey] = $leader;

                            if (!empty($shiftInput->leader_signature_qr)) {
                                $leadersQrByShift[$shiftKey] = $this->getQrCodePath($shiftInput->leader_signature_qr);
                            } else {
                                $leadersQrByShift[$shiftKey] = $this->getTtdQrCodePath($shiftInput->id, 'leader', $leader);
                            }
                        }
                    }
                }

                // Explicitly clear allInputs to free memory
                unset($allInputs);
                gc_collect_cycles();

                // 2. Prepare Production Per Hour Data (Robust Logic)
                $this->distributeInputProduksiToProduksiPerJam($planning->id);
                $produksiPerHour = ProduksiPerJam::where('planning_id', $planning->id)
                    ->orderByRaw("CASE shift WHEN 'pagi' THEN 1 WHEN 'siang' THEN 2 WHEN 'malam' THEN 3 END")
                    ->orderBy('jam')
                    ->get();

                $planningJam = \App\Models\PlanningProduksiJam::where('planning_id', $planning->id)
                    ->get()
                    ->keyBy(function ($item) {
                        $jamStr = $item->jam instanceof \Carbon\Carbon ? $item->jam->format('H:i') : $item->jam;
                        return $item->shift . '_' . $jamStr;
                    });

                $jamMapping = [
                    'pagi' => [
                        '07.00 - 08.00' => '07:00',
                        '08.00 - 09.00' => '08:00',
                        '09.00 - 10.00' => '09:00',
                        '10.00 - 11.00' => '10:00',
                        '11.00 - 12.00' => '11:00',
                        '12.00 - 13.00' => '12:00',
                        '13.00 - 14.00' => '13:00',
                        '14.00 - 15.00' => '14:00',
                    ],
                    'siang' => [
                        '15.00 - 16.00' => '15:00',
                        '16.00 - 17.00' => '16:00',
                        '17.00 - 18.00' => '17:00',
                        '18.00 - 19.00' => '18:00',
                        '19.00 - 20.00' => '19:00',
                        '20.00 - 21.00' => '20:00',
                        '21.00 - 22.00' => '21:00',
                        '22.00 - 23.00' => '22:00',
                    ],
                    'malam' => [
                        '23.00 - 24.00' => '23:00',
                        '24.00 - 01.00' => '00:00',
                        '01.00 - 02.00' => '01:00',
                        '02.00 - 03.00' => '02:00',
                        '03.00 - 04.00' => '03:00',
                        '04.00 - 05.00' => '04:00',
                        '05.00 - 06.00' => '05:00',
                        '06.00 - 07.00' => '06:00',
                    ],
                ];

                $targetShiftPagi = $planning->target_shift_pagi ?? 0;
                $targetShiftSiang = $planning->target_shift_siang ?? 0;
                $targetShiftMalam = $planning->target_shift_malam ?? 0;

                if ($targetShiftPagi == 0 && $targetShiftSiang == 0 && $targetShiftMalam == 0) {
                    $targetPerShift = round(($planning->qty_planning_produksi ?? 0) / 3);
                    $targetShiftPagi = $targetPerShift;
                    $targetShiftSiang = $targetPerShift;
                    $targetShiftMalam = $targetPerShift;
                }

                $targetPerJamPagi = $targetShiftPagi > 0 ? floor($targetShiftPagi / 8) : 0;
                $targetPerJamSiang = $targetShiftSiang > 0 ? floor($targetShiftSiang / 8) : 0;
                $targetPerJamMalam = $targetShiftMalam > 0 ? floor($targetShiftMalam / 8) : 0;

                $sisaTargetPagi = $targetShiftPagi - ($targetPerJamPagi * 8);
                $sisaTargetSiang = $targetShiftSiang - ($targetPerJamSiang * 8);
                $sisaTargetMalam = $targetShiftMalam - ($targetPerJamMalam * 8);

                $produksiPerJamData = ['pagi' => [], 'siang' => [], 'malam' => []];
                $totalPerShift = [
                    'pagi' => ['target' => $targetShiftPagi, 'ok' => 0, 'ng' => 0],
                    'siang' => ['target' => $targetShiftSiang, 'ok' => 0, 'ng' => 0],
                    'malam' => ['target' => $targetShiftMalam, 'ok' => 0, 'ng' => 0]
                ];

                $allJamSlots = [
                    'pagi' => ['07.00 - 08.00', '08.00 - 09.00', '09.00 - 10.00', '10.00 - 11.00', '11.00 - 12.00', '12.00 - 13.00', '13.00 - 14.00', '14.00 - 15.00'],
                    'siang' => ['15.00 - 16.00', '16.00 - 17.00', '17.00 - 18.00', '18.00 - 19.00', '19.00 - 20.00', '20.00 - 21.00', '21.00 - 22.00', '22.00 - 23.00'],
                    'malam' => ['23.00 - 24.00', '24.00 - 01.00', '01.00 - 02.00', '02.00 - 03.00', '03.00 - 04.00', '04.00 - 05.00', '05.00 - 06.00', '06.00 - 07.00'],
                ];

                $groupedProduksiPerJam = [];
                foreach ($produksiPerHour as $item) {
                    $key = $item->shift . '_' . $item->jam;
                    if (!isset($groupedProduksiPerJam[$key])) $groupedProduksiPerJam[$key] = [];
                    $groupedProduksiPerJam[$key][] = $item;
                }

                foreach ($groupedProduksiPerJam as $key => $items) {
                    $firstItem = $items[0];
                    $shift = $firstItem->shift;
                    $jam = $firstItem->jam;

                    $targetFromPlanning = null;
                    if (isset($jamMapping[$shift][$jam])) {
                        $jamKey = $jamMapping[$shift][$jam];
                        $planningJamKey = $shift . '_' . $jamKey;
                        if ($planningJam->has($planningJamKey)) $targetFromPlanning = $planningJam[$planningJamKey]->target;
                    }

                    $target = $targetFromPlanning;
                    if ($target === null || $target == 0) {
                        $target = $firstItem->target;
                        if ($target == 0) {
                            $jamIndex = array_search($jam, $allJamSlots[$shift] ?? []);
                            $jamIndex = $jamIndex !== false ? $jamIndex : 0;
                            switch ($shift) {
                                case 'pagi':
                                    $target = $targetPerJamPagi;
                                    if ($sisaTargetPagi > 0 && $jamIndex < $sisaTargetPagi) $target += 1;
                                    break;
                                case 'siang':
                                    $target = $targetPerJamSiang;
                                    if ($sisaTargetSiang > 0 && $jamIndex < $sisaTargetSiang) $target += 1;
                                    break;
                                case 'malam':
                                    $target = $targetPerJamMalam;
                                    if ($sisaTargetMalam > 0 && $jamIndex < $sisaTargetMalam) $target += 1;
                                    break;
                            }
                        }
                    }

                    $totalOkCount = 0;
                    $totalNgPart = 0;
                    $operatorCodes = [];
                    $totalNgSubpart = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
                    $masalah = '';
                    $tindakan = '';
                    $checkerPrd = '';
                    $checkerQc = '';

                    foreach ($items as $item) {
                        $okCount = (int)$item->getRawOriginal('ok_count');
                        $ngPart = (int)$item->getRawOriginal('ng_part');
                        $opCode = trim((string)$item->getRawOriginal('operator_code'));
                        $totalOkCount += $okCount;
                        $totalNgPart += $ngPart;
                        if (!empty($opCode) && !in_array($opCode, $operatorCodes)) $operatorCodes[] = $opCode;
                        for ($i = 1; $i <= 7; $i++) $totalNgSubpart[$i] += (int)$item->getRawOriginal('ng_subpart_' . $i);
                        if (!empty($item->masalah)) $masalah = $item->masalah;
                        if (!empty($item->tindakan)) $tindakan = $item->tindakan;
                        if (!empty($item->checker_prd)) $checkerPrd = $item->checker_prd;
                        if (!empty($item->checker_qc)) $checkerQc = $item->checker_qc;
                    }

                    $produksiPerJamData[$shift][$jam] = [
                        'target' => (int)$target,
                        'ok_count' => $totalOkCount,
                        'ng_part' => $totalNgPart,
                        'operator_code' => count($operatorCodes) > 1 ? implode('/', $operatorCodes) : (count($operatorCodes) == 1 ? $operatorCodes[0] : ''),
                        'ng_subpart_1' => $totalNgSubpart[1],
                        'ng_subpart_2' => $totalNgSubpart[2],
                        'ng_subpart_3' => $totalNgSubpart[3],
                        'ng_subpart_4' => $totalNgSubpart[4],
                        'ng_subpart_5' => $totalNgSubpart[5],
                        'ng_subpart_6' => $totalNgSubpart[6],
                        'ng_subpart_7' => $totalNgSubpart[7],
                        'masalah' => $masalah,
                        'tindakan' => $tindakan,
                        'checker_prd' => $checkerPrd,
                        'checker_qc' => $checkerQc,
                    ];
                    $totalPerShift[$shift]['ok'] += $totalOkCount;
                    $totalPerShift[$shift]['ng'] += $totalNgPart;
                }

                foreach ($allJamSlots as $shift => $jamSlots) {
                    foreach ($jamSlots as $jamSlot) {
                        if (!isset($produksiPerJamData[$shift][$jamSlot])) {
                            $targetFromPlanning = null;
                            if (isset($jamMapping[$shift][$jamSlot])) {
                                $jamKey = $jamMapping[$shift][$jamSlot];
                                $planningJamKey = $shift . '_' . $jamKey;
                                if ($planningJam->has($planningJamKey)) $targetFromPlanning = $planningJam[$planningJamKey]->target;
                            }
                            $target = $targetFromPlanning;
                            if ($target === null || $target == 0) {
                                $sisaTarget = 0;
                                $targetPerJam = 0;
                                switch ($shift) {
                                    case 'pagi':
                                        $sisaTarget = $sisaTargetPagi;
                                        $targetPerJam = $targetPerJamPagi;
                                        break;
                                    case 'siang':
                                        $sisaTarget = $sisaTargetSiang;
                                        $targetPerJam = $targetPerJamSiang;
                                        break;
                                    case 'malam':
                                        $sisaTarget = $sisaTargetMalam;
                                        $targetPerJam = $targetPerJamMalam;
                                        break;
                                }
                                $target = $targetPerJam;
                                $jamIndex = array_search($jamSlot, $jamSlots);
                                if ($jamIndex !== false && $sisaTarget > 0 && $jamIndex < $sisaTarget) $target += 1;
                            }
                            $produksiPerJamData[$shift][$jamSlot] = [
                                'target' => (int)$target,
                                'ok_count' => 0,
                                'ng_part' => 0,
                                'operator_code' => '',
                                'ng_subpart_1' => 0,
                                'ng_subpart_2' => 0,
                                'ng_subpart_3' => 0,
                                'ng_subpart_4' => 0,
                                'ng_subpart_5' => 0,
                                'ng_subpart_6' => 0,
                                'ng_subpart_7' => 0,
                                'masalah' => '',
                                'tindakan' => '',
                                'checker_prd' => '',
                                'checker_qc' => '',
                            ];
                        }
                    }
                }

                $totalOkFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)->where('status', 'approved')->sum('ok_count');
                $totalNgFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)->where('status', 'approved')->sum('ng_count');
                $totalOkFromProduksiPerJam = $totalPerShift['pagi']['ok'] + $totalPerShift['siang']['ok'] + $totalPerShift['malam']['ok'];
                $totalNgFromProduksiPerJam = $totalPerShift['pagi']['ng'] + $totalPerShift['siang']['ng'] + $totalPerShift['malam']['ng'];

                if ($totalOkFromProduksiPerJam == 0 && $totalNgFromProduksiPerJam == 0) {
                    $totalTarget = $targetShiftPagi + $targetShiftSiang + $targetShiftMalam;
                    if ($totalTarget > 0) {
                        $totalPerShift['pagi']['ok'] = round(($targetShiftPagi / $totalTarget) * $totalOkFromInput);
                        $totalPerShift['pagi']['ng'] = round(($targetShiftPagi / $totalTarget) * $totalNgFromInput);
                        $totalPerShift['siang']['ok'] = round(($targetShiftSiang / $totalTarget) * $totalOkFromInput);
                        $totalPerShift['siang']['ng'] = round(($targetShiftSiang / $totalTarget) * $totalOkFromInput);
                        $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                        $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                    }
                } else {
                    if ($totalOkFromInput > 0 || $totalNgFromInput > 0) {
                        $totalOkNgFromProduksiPerJam = $totalOkFromProduksiPerJam + $totalNgFromProduksiPerJam;
                        $totalOkNgFromInput = $totalOkFromInput + $totalNgFromInput;
                        if ($totalOkNgFromProduksiPerJam > 0 && abs($totalOkNgFromProduksiPerJam - $totalOkNgFromInput) > 0.1) {
                            $ratio = $totalOkNgFromInput / $totalOkNgFromProduksiPerJam;
                            $totalPerShift['pagi']['ok'] = round($totalPerShift['pagi']['ok'] * $ratio);
                            $totalPerShift['pagi']['ng'] = round($totalPerShift['pagi']['ng'] * $ratio);
                            $totalPerShift['siang']['ok'] = round($totalPerShift['siang']['ok'] * $ratio);
                            $totalPerShift['siang']['ng'] = round($totalPerShift['siang']['ng'] * $ratio);
                            $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                            $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                        }
                    }
                }

                $grandTotal = ['target' => $targetShiftPagi + $targetShiftSiang + $targetShiftMalam, 'ok' => $totalOkFromInput, 'ng' => $totalNgFromInput];


                // 3. Prepare Persiapan and NG Types
                $persiapanData = [];
                if ($data->ploting && $data->ploting->persiapanChecks) {
                    $persiapanData = $data->ploting->persiapanChecks->groupBy(function ($check) {
                        if (preg_match('/\((\w+)\)$/', $check->item_name, $matches)) {
                            return ucfirst(strtolower($matches[1]));
                        }
                        return 'Unknown';
                    })->map(function ($checks) {
                        return $checks->map(function ($check) {
                            return [
                                'item_name' => preg_replace('/\s*\([^)]+\)$/', '', $check->item_name),
                                'operator_checked' => $check->operator_checked,
                                'leader_checked' => $check->leader_checked,
                                'keterangan' => $check->keterangan
                            ];
                        })->toArray();
                    })->toArray();
                }

                $ngPartTypesData = [
                    'all_types' => \App\Models\NgPartType::active()->ordered()->get(),
                    'by_shift' => ['Shift 1' => [], 'Shift 2' => [], 'Shift 3' => []]
                ];

                if ($data->ngPartTypes) {
                    foreach ($data->ngPartTypes as $ngPart) {
                        if (($ngPart->pivot->quantity ?? 0) > 0) {
                            foreach (['Shift 1', 'Shift 2', 'Shift 3'] as $s) {
                                $ngPartTypesData['by_shift'][$s][] = [
                                    'name' => $ngPart->name,
                                    'code' => $ngPart->code,
                                    'quantity' => $ngPart->pivot->quantity
                                ];
                            }
                        }
                    }
                }

                $allData[] = [
                    'data' => $data,
                    'operatorsByShift' => $operatorsByShift,
                    'operatorsQrByShift' => $operatorsQrByShift,
                    'leadersByShift' => $leadersByShift,
                    'leadersQrByShift' => $leadersQrByShift,
                    'produksiPerJamData' => $produksiPerJamData,
                    'totalPerShift' => $totalPerShift,
                    'grandTotal' => $grandTotal,
                    'persiapanData' => $persiapanData,
                    'ngPartTypesData' => $ngPartTypesData
                ];
            }

            $logoBase64 = $this->getLogoBase64();
            $options = [
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => false,
                'chroot' => [public_path(), storage_path()],
            ];

            $pdf = Pdf::loadView('laporan-produksi.cetak', compact('allData', 'logoBase64'));
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions($options);

            // Clear variables from memory before streaming
            unset($allData);
            gc_collect_cycles();

            return $pdf->stream('laporan-produksi-semua-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('laporan-produksi.index')
                ->with('error', 'Gagal menghasilkan PDF: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '120');

        try {
            $data = InputProduksi::with([
                'planning.mesin',
                'planning.meja',
                'planning.moldshop',
                'planning.part',
                'planning.plotings.operator',
                'planning.plotings.grup.leader',
                'planning.plotings.persiapanChecks',
                'ploting.operator',
                'ploting.grup.leader',
                'ploting.persiapanChecks',
                'approver',
                'ngPartTypes'
            ])->where('status', 'approved')->findOrFail($id);

            // Tentukan shift dari data ini
            $planning = $data->planning;
            $shift = $this->normalizeShift($planning->shift ?? null);

            // Jika shift tidak jelas dari planning, tentukan dari waktu created_at
            if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                $hour = $data->created_at->hour;
                if ($hour >= 7 && $hour < 15) {
                    $shift = 'pagi';
                } elseif ($hour >= 15 && $hour < 23) {
                    $shift = 'siang';
                } else {
                    $shift = 'malam';
                }
            }

            // Kelompokkan operator per shift dari semua plotings di planning ini
            $operatorsByShift = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => []
            ];
            $operatorsQrByShift = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => []
            ];

            // Ambil semua input produksi yang sudah approved untuk planning ini
            $allInputs = InputProduksi::where('planning_id', $planning->id)
                ->where('status', 'approved')
                ->with(['ploting.operator', 'ploting.grup'])
                ->get();

            foreach ($allInputs as $input) {
                $ploting = $input->ploting;
                if (!$ploting || !$ploting->operator) continue;

                // Tentukan shift dari jadwal kerja operator (prioritas utama)
                $inputShift = null;
                if ($ploting && $ploting->operator) {
                    $tanggalInput = $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d');
                    $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                        ->whereDate('tanggal', $tanggalInput)
                        ->where('status', 'aktif')
                        ->first();

                    if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                        $inputShift = strtolower($jadwal->shift);
                    }
                }

                // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                    $inputShift = $this->normalizeShift($planning->shift ?? null);
                    if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                        $hour = $input->created_at->hour;
                        if ($hour >= 7 && $hour < 15) {
                            $inputShift = 'pagi';
                        } elseif ($hour >= 15 && $hour < 23) {
                            $inputShift = 'siang';
                        } else {
                            $inputShift = 'malam';
                        }
                    }
                }

                // Map shift ke shift key
                $shiftKey = null;
                if ($inputShift === 'pagi') {
                    $shiftKey = 'shift_1';
                } elseif ($inputShift === 'siang') {
                    $shiftKey = 'shift_2';
                } elseif ($inputShift === 'malam') {
                    $shiftKey = 'shift_3';
                }

                if ($shiftKey && $ploting->operator) {
                    // Cek apakah operator sudah ada di shift ini
                    $operatorExists = false;
                    foreach ($operatorsByShift[$shiftKey] as $existingOperator) {
                        if ($existingOperator['id'] === $ploting->operator->id) {
                            $operatorExists = true;
                            break;
                        }
                    }

                    if (!$operatorExists) {
                        // Operator QR - gunakan QR dari input produksi ini atau generate baru
                        $operatorQrBase64 = null;
                        if (!empty($input->operator_signature_qr)) {
                            $operatorQrBase64 = $this->getQrCodeBase64($input->operator_signature_qr);
                        } else {
                            $operatorQrBase64 = $this->getTtdQrCodeBase64($input->id, 'operator', $ploting->operator);
                        }

                        $operatorsByShift[$shiftKey][] = [
                            'id' => $ploting->operator->id,
                            'nama' => $ploting->operator->nama ?? '-',
                            'nik' => $ploting->operator->nik ?? '-'
                        ];
                        $operatorsQrByShift[$shiftKey][] = $operatorQrBase64;
                    }
                }
            }

            // Get leaders per shift
            $leadersByShift = [
                'shift_1' => null,
                'shift_2' => null,
                'shift_3' => null
            ];
            $leadersQrByShift = [
                'shift_1' => null,
                'shift_2' => null,
                'shift_3' => null
            ];

            foreach (['shift_1', 'shift_2', 'shift_3'] as $shiftKey) {
                // Cari input produksi untuk shift ini
                $shiftInput = null;
                foreach ($allInputs as $input) {
                    $ploting = $input->ploting;

                    // Tentukan shift dari jadwal kerja operator (prioritas utama)
                    $inputShift = null;
                    if ($ploting && $ploting->operator) {
                        $tanggalInput = $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d');
                        $jadwal = \App\Models\JadwalKerja::where('user_id', $ploting->operator->id)
                            ->whereDate('tanggal', $tanggalInput)
                            ->where('status', 'aktif')
                            ->first();

                        if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                            $inputShift = strtolower($jadwal->shift);
                        }
                    }

                    // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                    if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                        $inputShift = $this->normalizeShift($planning->shift ?? null);
                        if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                            $hour = $input->created_at->hour;
                            if ($hour >= 7 && $hour < 15) {
                                $inputShift = 'pagi';
                            } elseif ($hour >= 15 && $hour < 23) {
                                $inputShift = 'siang';
                            } else {
                                $inputShift = 'malam';
                            }
                        }
                    }

                    $expectedShift = str_replace('shift_', '', $shiftKey);
                    if (($expectedShift === '1' && $inputShift === 'pagi') ||
                        ($expectedShift === '2' && $inputShift === 'siang') ||
                        ($expectedShift === '3' && $inputShift === 'malam')
                    ) {
                        $shiftInput = $input;
                        break;
                    }
                }

                if ($shiftInput) {
                    $leader = $this->getLeaderByShiftAndGrup($shiftInput);
                    if ($leader) {
                        $leadersByShift[$shiftKey] = $leader;

                        // Leader QR
                        if (!empty($shiftInput->leader_signature_qr)) {
                            $leadersQrByShift[$shiftKey] = $this->getQrCodePath($shiftInput->leader_signature_qr);
                        } else {
                            $leadersQrByShift[$shiftKey] = $this->getTtdQrCodePath($shiftInput->id, 'leader', $leader);
                        }
                    }
                }
            }

            // Prepare QR code untuk operator dan leader dari data ini (untuk kompatibilitas)
            if (empty($data->operator_signature_qr)) {
                $operator = $data->ploting->operator ?? null;
                $data->operator_qr_base64 = $this->getTtdQrCodeBase64($data->id, 'operator', $operator);
            } else {
                $data->operator_qr_base64 = $this->getQrCodeBase64($data->operator_signature_qr);
            }

            // Get leader berdasarkan shift dan grup dari ploting
            $leader = $this->getLeaderByShiftAndGrup($data);
            $data->shift_leader = $leader;

            if (empty($data->leader_signature_qr)) {
                $data->leader_qr_base64 = $this->getTtdQrCodeBase64($data->id, 'leader', $leader);
            } else {
                $data->leader_qr_base64 = $this->getQrCodeBase64($data->leader_signature_qr);
            }

            // DISTRIBUSI DATA OK/NG DARI INPUT PRODUKSI KE PRODUKSI PER JAM
            $this->distributeInputProduksiToProduksiPerJam($planning->id);

            // Ambil data produksi per jam untuk planning ini
            $produksiPerJam = ProduksiPerJam::where('planning_id', $planning->id)
                ->orderByRaw("CASE shift WHEN 'pagi' THEN 1 WHEN 'siang' THEN 2 WHEN 'malam' THEN 3 END")
                ->orderBy('jam')
                ->get();

            // Ambil target per jam dari planning_produksi_jam
            $planningJam = \App\Models\PlanningProduksiJam::where('planning_id', $planning->id)
                ->get()
                ->keyBy(function ($item) {
                    $jamStr = $item->jam instanceof \Carbon\Carbon
                        ? $item->jam->format('H:i')
                        : $item->jam;
                    return $item->shift . '_' . $jamStr;
                });

            // Mapping jam
            $jamMapping = [
                'pagi' => [
                    '07.00 - 08.00' => '07:00',
                    '08.00 - 09.00' => '08:00',
                    '09.00 - 10.00' => '09:00',
                    '10.00 - 11.00' => '10:00',
                    '11.00 - 12.00' => '11:00',
                    '12.00 - 13.00' => '12:00',
                    '13.00 - 14.00' => '13:00',
                    '14.00 - 15.00' => '14:00',
                ],
                'siang' => [
                    '15.00 - 16.00' => '15:00',
                    '16.00 - 17.00' => '16:00',
                    '17.00 - 18.00' => '17:00',
                    '18.00 - 19.00' => '18:00',
                    '19.00 - 20.00' => '19:00',
                    '20.00 - 21.00' => '20:00',
                    '21.00 - 22.00' => '21:00',
                    '22.00 - 23.00' => '22:00',
                ],
                'malam' => [
                    '23.00 - 24.00' => '23:00',
                    '24.00 - 01.00' => '00:00',
                    '01.00 - 02.00' => '01:00',
                    '02.00 - 03.00' => '02:00',
                    '03.00 - 04.00' => '03:00',
                    '04.00 - 05.00' => '04:00',
                    '05.00 - 06.00' => '05:00',
                    '06.00 - 07.00' => '06:00',
                ],
            ];

            // Hitung target per shift
            $targetShiftPagi = $planning->target_shift_pagi ?? 0;
            $targetShiftSiang = $planning->target_shift_siang ?? 0;
            $targetShiftMalam = $planning->target_shift_malam ?? 0;

            if ($targetShiftPagi == 0 && $targetShiftSiang == 0 && $targetShiftMalam == 0) {
                $targetPerShift = round(($planning->qty_planning_produksi ?? 0) / 3);
                $targetShiftPagi = $targetPerShift;
                $targetShiftSiang = $targetPerShift;
                $targetShiftMalam = $targetPerShift;
            }

            $targetPerJamPagi = $targetShiftPagi > 0 ? floor($targetShiftPagi / 8) : 0;
            $targetPerJamSiang = $targetShiftSiang > 0 ? floor($targetShiftSiang / 8) : 0;
            $targetPerJamMalam = $targetShiftMalam > 0 ? floor($targetShiftMalam / 8) : 0;

            $sisaTargetPagi = $targetShiftPagi - ($targetPerJamPagi * 8);
            $sisaTargetSiang = $targetShiftSiang - ($targetPerJamSiang * 8);
            $sisaTargetMalam = $targetShiftMalam - ($targetPerJamMalam * 8);

            $produksiPerJamData = [
                'pagi' => [],
                'siang' => [],
                'malam' => []
            ];

            $totalPerShift = [
                'pagi' => ['target' => 0, 'ok' => 0, 'ng' => 0],
                'siang' => ['target' => 0, 'ok' => 0, 'ng' => 0],
                'malam' => ['target' => 0, 'ok' => 0, 'ng' => 0]
            ];

            $allJamSlots = [
                'pagi' => array_keys($jamMapping['pagi']),
                'siang' => array_keys($jamMapping['siang']),
                'malam' => array_keys($jamMapping['malam']),
            ];

            $groupedProduksiPerJam = [];
            foreach ($produksiPerJam as $item) {
                $key = $item->shift . '_' . $item->jam;
                if (!isset($groupedProduksiPerJam[$key])) {
                    $groupedProduksiPerJam[$key] = [];
                }
                $groupedProduksiPerJam[$key][] = $item;
            }

            foreach ($groupedProduksiPerJam as $key => $items) {
                $firstItem = $items[0];
                $shift = $firstItem->shift;
                $jam = $firstItem->jam;

                $targetFromPlanning = null;
                if (isset($jamMapping[$shift][$jam])) {
                    $jamKey = $jamMapping[$shift][$jam];
                    $planningJamKey = $shift . '_' . $jamKey;
                    if ($planningJam->has($planningJamKey)) {
                        $targetFromPlanning = $planningJam[$planningJamKey]->target;
                    }
                }

                $target = $targetFromPlanning;
                if ($target === null || $target == 0) {
                    $target = $firstItem->target;
                    if ($target == 0) {
                        $jamIndex = array_search($jam, $allJamSlots[$shift] ?? []);
                        $jamIndex = $jamIndex !== false ? $jamIndex : 0;

                        switch ($shift) {
                            case 'pagi':
                                $target = $targetPerJamPagi;
                                if ($sisaTargetPagi > 0 && $jamIndex < $sisaTargetPagi) $target += 1;
                                break;
                            case 'siang':
                                $target = $targetPerJamSiang;
                                if ($sisaTargetSiang > 0 && $jamIndex < $sisaTargetSiang) $target += 1;
                                break;
                            case 'malam':
                                $target = $targetPerJamMalam;
                                if ($sisaTargetMalam > 0 && $jamIndex < $sisaTargetMalam) $target += 1;
                                break;
                        }
                    }
                }

                $totalOkCount = 0;
                $totalNgPart = 0;
                $operatorCodes = [];
                $totalNgSubpart = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
                $masalah = '';
                $tindakan = '';
                $checkerPrd = '';
                $checkerQc = '';

                foreach ($items as $item) {
                    $okCount = is_numeric($item->getRawOriginal('ok_count')) ? (int)$item->getRawOriginal('ok_count') : 0;
                    $ngPart = is_numeric($item->getRawOriginal('ng_part')) ? (int)$item->getRawOriginal('ng_part') : 0;
                    $operatorCode = is_string($item->getRawOriginal('operator_code')) ? trim($item->getRawOriginal('operator_code')) : '';

                    $totalOkCount += $okCount;
                    $totalNgPart += $ngPart;

                    if (!empty($operatorCode) && !in_array($operatorCode, $operatorCodes)) {
                        $operatorCodes[] = $operatorCode;
                    }

                    for ($i = 1; $i <= 7; $i++) {
                        $ngSubpart = is_numeric($item->getRawOriginal('ng_subpart_' . $i)) ? (int)$item->getRawOriginal('ng_subpart_' . $i) : 0;
                        $totalNgSubpart[$i] += $ngSubpart;
                    }

                    if (!empty($item->masalah)) $masalah = (string)$item->masalah;
                    if (!empty($item->tindakan)) $tindakan = (string)$item->tindakan;
                    if (!empty($item->checker_prd)) $checkerPrd = (string)$item->checker_prd;
                    if (!empty($item->checker_qc)) $checkerQc = (string)$item->checker_qc;
                }

                $operatorCodeCombined = count($operatorCodes) > 1 ? implode('/', $operatorCodes) : (count($operatorCodes) == 1 ? $operatorCodes[0] : '');

                $produksiPerJamData[$shift][$jam] = [
                    'target' => (int)$target,
                    'ok_count' => $totalOkCount,
                    'ng_part' => $totalNgPart,
                    'operator_code' => $operatorCodeCombined,
                    'ng_subpart_1' => $totalNgSubpart[1],
                    'ng_subpart_2' => $totalNgSubpart[2],
                    'ng_subpart_3' => $totalNgSubpart[3],
                    'ng_subpart_4' => $totalNgSubpart[4],
                    'ng_subpart_5' => $totalNgSubpart[5],
                    'ng_subpart_6' => $totalNgSubpart[6],
                    'ng_subpart_7' => $totalNgSubpart[7],
                    'masalah' => $masalah,
                    'tindakan' => $tindakan,
                    'checker_prd' => $checkerPrd,
                    'checker_qc' => $checkerQc,
                ];

                $totalPerShift[$shift]['ok'] += $totalOkCount;
                $totalPerShift[$shift]['ng'] += $totalNgPart;
            }

            // Isi jam yang belum ada input
            foreach ($allJamSlots as $shift => $jamSlots) {
                foreach ($jamSlots as $jamSlot) {
                    if (!isset($produksiPerJamData[$shift][$jamSlot])) {
                        $targetFromPlanning = null;
                        if (isset($jamMapping[$shift][$jamSlot])) {
                            $jamKey = $jamMapping[$shift][$jamSlot];
                            $planningJamKey = $shift . '_' . $jamKey;
                            if ($planningJam->has($planningJamKey)) {
                                $targetFromPlanning = $planningJam[$planningJamKey]->target;
                            }
                        }

                        $target = $targetFromPlanning;
                        if ($target === null || $target == 0) {
                            $sisaTarget = 0;
                            $targetPerJam = 0;
                            switch ($shift) {
                                case 'pagi':
                                    $sisaTarget = $sisaTargetPagi;
                                    $targetPerJam = $targetPerJamPagi;
                                    break;
                                case 'siang':
                                    $sisaTarget = $sisaTargetSiang;
                                    $targetPerJam = $targetPerJamSiang;
                                    break;
                                case 'malam':
                                    $sisaTarget = $sisaTargetMalam;
                                    $targetPerJam = $targetPerJamMalam;
                                    break;
                            }

                            $target = $targetPerJam;
                            $jamIndex = array_search($jamSlot, $jamSlots);
                            if ($jamIndex !== false && $sisaTarget > 0 && $jamIndex < $sisaTarget) {
                                $target += 1;
                            }
                        }

                        $produksiPerJamData[$shift][$jamSlot] = [
                            'target' => (int)$target,
                            'ok_count' => 0,
                            'ng_part' => 0,
                            'operator_code' => '',
                            'ng_subpart_1' => 0,
                            'ng_subpart_2' => 0,
                            'ng_subpart_3' => 0,
                            'ng_subpart_4' => 0,
                            'ng_subpart_5' => 0,
                            'ng_subpart_6' => 0,
                            'ng_subpart_7' => 0,
                            'masalah' => '',
                            'tindakan' => '',
                            'checker_prd' => '',
                            'checker_qc' => '',
                        ];
                    }
                }
            }

            $totalPerShift['pagi']['target'] = $targetShiftPagi;
            $totalPerShift['siang']['target'] = $targetShiftSiang;
            $totalPerShift['malam']['target'] = $targetShiftMalam;

            $totalOkFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)->sum('ok_count');
            $totalNgFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)->sum('ng_count');

            $totalOkFromProduksiPerJam = $totalPerShift['pagi']['ok'] + $totalPerShift['siang']['ok'] + $totalPerShift['malam']['ok'];
            $totalNgFromProduksiPerJam = $totalPerShift['pagi']['ng'] + $totalPerShift['siang']['ng'] + $totalPerShift['malam']['ng'];

            if ($totalOkFromProduksiPerJam == 0 && $totalNgFromProduksiPerJam == 0) {
                $totalTarget = $targetShiftPagi + $targetShiftSiang + $targetShiftMalam;
                if ($totalTarget > 0) {
                    $totalPerShift['pagi']['ok'] = round(($targetShiftPagi / $totalTarget) * $totalOkFromInput);
                    $totalPerShift['pagi']['ng'] = round(($targetShiftPagi / $totalTarget) * $totalNgFromInput);
                    $totalPerShift['siang']['ok'] = round(($targetShiftSiang / $totalTarget) * $totalOkFromInput);
                    $totalPerShift['siang']['ng'] = round(($targetShiftSiang / $totalTarget) * $totalNgFromInput);
                    $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                    $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                }
            } else {
                if ($totalOkFromInput > 0 || $totalNgFromInput > 0) {
                    $totalOkNgFromProduksiPerJam = $totalOkFromProduksiPerJam + $totalNgFromProduksiPerJam;
                    $totalOkNgFromInput = $totalOkFromInput + $totalNgFromInput;

                    if ($totalOkNgFromProduksiPerJam > 0 && abs($totalOkNgFromProduksiPerJam - $totalOkNgFromInput) > 0.1) {
                        $ratio = $totalOkNgFromInput / $totalOkNgFromProduksiPerJam;
                        $totalPerShift['pagi']['ok'] = round($totalPerShift['pagi']['ok'] * $ratio);
                        $totalPerShift['pagi']['ng'] = round($totalPerShift['pagi']['ng'] * $ratio);
                        $totalPerShift['siang']['ok'] = round($totalPerShift['siang']['ok'] * $ratio);
                        $totalPerShift['siang']['ng'] = round($totalPerShift['siang']['ng'] * $ratio);
                        $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                        $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                    }
                }
            }

            $grandTotal = [
                'target' => $targetShiftPagi + $targetShiftSiang + $targetShiftMalam,
                'ok' => $totalOkFromInput,
                'ng' => $totalNgFromInput
            ];

            $persiapanData = [];
            if ($data->ploting && $data->ploting->persiapanChecks) {
                $persiapanData = $data->ploting->persiapanChecks->groupBy(function ($check) {
                    if (preg_match('/\((\w+)\)$/', $check->item_name, $matches)) {
                        return ucfirst(strtolower($matches[1]));
                    }
                    return 'Unknown';
                })->map(function ($checks, $shift) {
                    return $checks->map(function ($check) {
                        $itemName = preg_replace('/\s*\([^)]+\)$/', '', $check->item_name);
                        return [
                            'item_name' => $itemName,
                            'operator_checked' => $check->operator_checked,
                            'leader_checked' => $check->leader_checked,
                            'keterangan' => $check->keterangan
                        ];
                    })->toArray();
                })->toArray();
            }

            $ngPartTypesData = [];
            try {
                $allNgPartTypes = \App\Models\NgPartType::active()->ordered()->get();
                $ngPartTypesByShift = ['Shift 1' => [], 'Shift 2' => [], 'Shift 3' => []];

                if ($data->ngPartTypes && $data->ngPartTypes->count() > 0) {
                    foreach ($data->ngPartTypes as $ngPart) {
                        $quantity = $ngPart->pivot->quantity ?? 0;
                        if ($quantity > 0) {
                            foreach (['Shift 1', 'Shift 2', 'Shift 3'] as $shift) {
                                $ngPartTypesByShift[$shift][] = [
                                    'name' => $ngPart->name,
                                    'code' => $ngPart->code,
                                    'quantity' => $quantity
                                ];
                            }
                        }
                    }
                }

                $ngPartTypesData = [
                    'all_types' => $allNgPartTypes,
                    'by_shift' => $ngPartTypesByShift
                ];
            } catch (\Exception $e) {
                $ngPartTypesData = [
                    'all_types' => collect([]),
                    'by_shift' => ['Shift 1' => [], 'Shift 2' => [], 'Shift 3' => []]
                ];
            }

            // Get all NG Part Types for reference
            $allNgPartTypes = \App\Models\NgPartType::active()->ordered()->get();

            return view('laporan-produksi.show', compact(
                'data',
                'allNgPartTypes',
                'operatorsByShift',
                'operatorsQrByShift',
                'leadersByShift',
                'leadersQrByShift',
                'produksiPerJamData',
                'totalPerShift',
                'grandTotal',
                'persiapanData',
                'ngPartTypesData'
            ));
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            return redirect()->route('laporan-produksi.index')
                ->with('error', 'Gagal memuat data detail: ' . $e->getMessage());
        }
    }

    /**
     * Get detail data for modal (AJAX)
     */
    public function getDetail($id)
    {
        try {
            $data = InputProduksi::with([
                'planning.mesin',
                'planning.moldshop',
                'planning.part',
                'planning.plotings.operator',
                'planning.plotings.persiapanChecks',
                'ploting.operator',
                'ploting.persiapanChecks',
                'approver',
                'ngPartTypes'
            ])->where('status', 'approved')->findOrFail($id);

            // Get NG categories
            $ngCategories = [];
            if ($data->ng_count > 0) {
                $ngCategories = $data->getTotalNgByCategoryAttribute();
            }

            // Get NG Part Types detail
            $ngPartTypesDetail = [];
            if ($data->ngPartTypes && $data->ngPartTypes->count() > 0) {
                $ngPartTypesDetail = $data->ngPartTypes->map(function ($ngPart) {
                    return [
                        'id' => $ngPart->id,
                        'name' => $ngPart->name,
                        'code' => $ngPart->code,
                        'quantity' => $ngPart->pivot->quantity ?? 0,
                        'category' => $ngPart->pivot->category ?? null
                    ];
                })->toArray();
            }

            // Get Persiapan Produksi data
            $persiapanData = [];
            if ($data->ploting && $data->ploting->persiapanChecks) {
                $persiapanData = $data->ploting->persiapanChecks->groupBy(function ($check) {
                    // Extract shift from item_name (e.g., "Briefing (Pagi)" -> "Pagi")
                    if (preg_match('/\((\w+)\)$/', $check->item_name, $matches)) {
                        return $matches[1];
                    }
                    return 'Unknown';
                })->map(function ($checks, $shift) {
                    return $checks->map(function ($check) {
                        // Remove shift suffix from item_name
                        $itemName = preg_replace('/\s*\([^)]+\)$/', '', $check->item_name);
                        return [
                            'item_name' => $itemName,
                            'operator_checked' => $check->operator_checked,
                            'leader_checked' => $check->leader_checked,
                            'keterangan' => $check->keterangan
                        ];
                    })->toArray();
                })->toArray();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $data->id,
                    'tanggal' => $data->created_at->format('d-m-Y H:i'),
                    'mesin' => $data->planning->mesin->nama_mesin ?? '-',
                    'part' => $data->planning->part->nama_part ?? '-',
                    'part_code' => $data->planning->part->no_parts ?? '-',
                    'customer' => optional($data->planning->moldshop)->customer ?? optional($data->planning->moldshop)->customer_name ?? '-',
                    'mold' => optional($data->planning->moldshop)->nama_mold ?? optional($data->planning->moldshop)->kode_mold ?? '-',
                    'qty_planning' => number_format($data->planning->qty_planning_produksi ?? 0),
                    'ok_count' => number_format($data->ok_count),
                    'ng_count' => number_format($data->ng_count),
                    'total' => number_format($data->ok_count + $data->ng_count),
                    'operators' => $data->planning->plotings->map(function ($ploting) {
                        return $ploting->operator->nama ?? '-';
                    })->toArray(),
                    'ng_categories' => $ngCategories,
                    'ng_part_types' => $ngPartTypesDetail,
                    'persiapan_data' => $persiapanData,
                    'cetak_url' => route('laporan-produksi.cetak-single', $data->id)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data detail'
            ], 500);
        }
    }

    public function cetakSingle($id)
    {
        // Optimize memory and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '120');

        try {
            $data = InputProduksi::with([
                'planning.mesin',
                'planning.moldshop',
                'planning.part',
                'planning.meja',
                'planning.plotings.operator',
                'planning.plotings.grup.leader',
                'planning.plotings.persiapanChecks',
                'ploting.operator',
                'ploting.grup.leader',
                'ploting.persiapanChecks',
                'approver',
                'ngPartTypes'
            ])->where('status', 'approved')->findOrFail($id);

            // Tentukan shift dari data ini
            $planning = $data->planning;
            $shift = $this->normalizeShift($planning->shift ?? null);

            // Jika shift tidak jelas dari planning, tentukan dari waktu created_at
            if (!$shift || !in_array($shift, ['pagi', 'siang', 'malam'])) {
                $hour = $data->created_at->hour;
                if ($hour >= 7 && $hour < 15) {
                    $shift = 'pagi';
                } elseif ($hour >= 15 && $hour < 23) {
                    $shift = 'siang';
                } else {
                    $shift = 'malam';
                }
            }

            // Kelompokkan operator per shift dari semua plotings di planning ini
            $operatorsByShift = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => []
            ];
            $operatorsQrByShift = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => []
            ];

            // Ambil semua input produksi yang sudah approved untuk planning ini
            $allInputs = InputProduksi::where('planning_id', $planning->id)
                ->where('status', 'approved')
                ->with(['ploting.operator', 'ploting.grup'])
                ->get();

            // Optimization: Batch fetch JadwalKerja for all operators and dates in allInputs
            $operatorIds = $allInputs->pluck('ploting.operator.id')->unique()->filter()->toArray();
            $dates = $allInputs->map(fn($input) => $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d'))->unique()->toArray();

            $jadwalKerjas = \App\Models\JadwalKerja::whereIn('user_id', $operatorIds)
                ->whereIn(DB::raw('DATE(tanggal)'), $dates)
                ->where('status', 'aktif')
                ->get()
                ->groupBy(['user_id', function ($item) {
                    return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
                }]);

            // Track shift inputs for leaders
            $shiftInputs = [
                'shift_1' => null,
                'shift_2' => null,
                'shift_3' => null
            ];

            foreach ($allInputs as $input) {
                $ploting = $input->ploting;
                if (!$ploting || !$ploting->operator) continue;

                // Tentukan shift dari jadwal kerja operator (prioritas utama)
                $inputShift = null;
                $tanggalInput = $input->created_at ? $input->created_at->format('Y-m-d') : date('Y-m-d');

                $jadwal = $jadwalKerjas->get($ploting->operator->id)?->get($tanggalInput)?->first();

                if ($jadwal && $jadwal->shift && $jadwal->shift !== 'off') {
                    $inputShift = strtolower($jadwal->shift);
                }

                // Jika shift tidak ditemukan dari jadwal, gunakan planning atau waktu created_at
                if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                    $inputShift = $this->normalizeShift($planning->shift ?? null);
                    if (!$inputShift || !in_array($inputShift, ['pagi', 'siang', 'malam'])) {
                        $hour = $input->created_at->hour;
                        if ($hour >= 7 && $hour < 15) {
                            $inputShift = 'pagi';
                        } elseif ($hour >= 15 && $hour < 23) {
                            $inputShift = 'siang';
                        } else {
                            $inputShift = 'malam';
                        }
                    }
                }

                // Map shift ke shift key
                $shiftKey = null;
                if ($inputShift === 'pagi') {
                    $shiftKey = 'shift_1';
                } elseif ($inputShift === 'siang') {
                    $shiftKey = 'shift_2';
                } elseif ($inputShift === 'malam') {
                    $shiftKey = 'shift_3';
                }

                if ($shiftKey) {
                    // Simpan input untuk shift leader (ambil yang pertama kali ditemukan untuk shift ini)
                    if (!$shiftInputs[$shiftKey]) {
                        $shiftInputs[$shiftKey] = $input;
                    }

                    if ($ploting->operator) {
                        // Cek apakah operator sudah ada di shift ini
                        $operatorExists = false;
                        foreach ($operatorsByShift[$shiftKey] as $existingOperator) {
                            if ($existingOperator['id'] === $ploting->operator->id) {
                                $operatorExists = true;
                                break;
                            }
                        }

                        if (!$operatorExists) {
                            // Operator QR - gunakan QR dari input produksi ini atau generate baru
                            $operatorQrPath = null;
                            if (!empty($input->operator_signature_qr)) {
                                $operatorQrPath = $this->getQrCodePath($input->operator_signature_qr);
                            } else {
                                $operatorQrPath = $this->getTtdQrCodePath($input->id, 'operator', $ploting->operator);
                            }

                            $operatorsByShift[$shiftKey][] = [
                                'id' => $ploting->operator->id,
                                'nama' => $ploting->operator->nama ?? '-',
                                'nik' => $ploting->operator->nik ?? '-'
                            ];
                            $operatorsQrByShift[$shiftKey][] = $operatorQrPath;
                        }
                    }
                }
            }

            // Get leaders per shift
            $leadersByShift = [
                'shift_1' => null,
                'shift_2' => null,
                'shift_3' => null
            ];
            $leadersQrByShift = [
                'shift_1' => null,
                'shift_2' => null,
                'shift_3' => null
            ];

            foreach (['shift_1', 'shift_2', 'shift_3'] as $shiftKey) {
                $shiftInput = $shiftInputs[$shiftKey];

                if ($shiftInput) {
                    $leader = $this->getLeaderByShiftAndGrup($shiftInput);
                    if ($leader) {
                        $leadersByShift[$shiftKey] = $leader;

                        if (!empty($shiftInput->leader_signature_qr)) {
                            $leadersQrByShift[$shiftKey] = $this->getQrCodePath($shiftInput->leader_signature_qr);
                        } else {
                            $leadersQrByShift[$shiftKey] = $this->getTtdQrCodePath($shiftInput->id, 'leader', $leader);
                        }
                    }
                }
            }

            // Prepare QR code untuk operator dan leader dari data ini (untuk kompatibilitas)
            if (empty($data->operator_signature_qr)) {
                $operator = $data->ploting->operator ?? null;
                $data->operator_qr_base64 = $this->getTtdQrCodeBase64($data->id, 'operator', $operator);
            } else {
                $data->operator_qr_base64 = $this->getQrCodeBase64($data->operator_signature_qr);
            }

            // Get leader berdasarkan shift dan grup dari ploting
            $leader = $this->getLeaderByShiftAndGrup($data);
            $data->shift_leader = $leader;

            if (empty($data->leader_signature_qr)) {
                $data->leader_qr_base64 = $this->getTtdQrCodeBase64($data->id, 'leader', $leader);
            } else {
                $data->leader_qr_base64 = $this->getQrCodeBase64($data->leader_signature_qr);
            }

            // Prepare logo path
            $logoBase64 = $this->getLogoBase64();

            // Set DomPDF options as array
            $options = [
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => false,
                'chroot' => [public_path(), storage_path()],
                'debugKeepTemp' => true,
                'debugCss' => false,
                'debugLayout' => false,
            ];

            try {
                // ✅ DISTRIBUSI DATA OK/NG DARI INPUT PRODUKSI KE PRODUKSI PER JAM
                // CATATAN: Data produksi_per_jam sudah tersimpan secara realtime melalui saveProduksiPerJamRealtime
                // ketika tombol OK/NG diklik di input-produksi/index.blade.php
                // Fungsi ini hanya untuk memastikan tidak ada data yang terlewat (fallback)
                // Prioritas utama: Gunakan data dari produksi_per_jam yang sudah ada (realtime)
                $this->distributeInputProduksiToProduksiPerJam($planning->id);

                // Ambil data produksi per jam untuk planning ini (REALTIME - selalu ambil data terbaru)
                // Urutkan berdasarkan shift dan jam untuk memastikan data muncul di urutan yang benar
                $produksiPerJam = ProduksiPerJam::where('planning_id', $planning->id)
                    ->orderByRaw("CASE shift WHEN 'pagi' THEN 1 WHEN 'siang' THEN 2 WHEN 'malam' THEN 3 END")
                    ->orderBy('jam')
                    ->get();

                // Ambil target per jam dari planning_produksi_jam
                $planningJam = \App\Models\PlanningProduksiJam::where('planning_id', $planning->id)
                    ->get()
                    ->keyBy(function ($item) {
                        // Buat key dari shift dan jam (format: "pagi_07:00" atau "pagi_07.00 - 08.00")
                        $jamStr = $item->jam instanceof \Carbon\Carbon
                            ? $item->jam->format('H:i')
                            : $item->jam;
                        return $item->shift . '_' . $jamStr;
                    });

                // Mapping jam dari format "07.00 - 08.00" ke format "07:00" untuk matching
                $jamMapping = [
                    'pagi' => [
                        '07.00 - 08.00' => '07:00',
                        '08.00 - 09.00' => '08:00',
                        '09.00 - 10.00' => '09:00',
                        '10.00 - 11.00' => '10:00',
                        '11.00 - 12.00' => '11:00',
                        '12.00 - 13.00' => '12:00',
                        '13.00 - 14.00' => '13:00',
                        '14.00 - 15.00' => '14:00',
                    ],
                    'siang' => [
                        '15.00 - 16.00' => '15:00',
                        '16.00 - 17.00' => '16:00',
                        '17.00 - 18.00' => '17:00',
                        '18.00 - 19.00' => '18:00',
                        '19.00 - 20.00' => '19:00',
                        '20.00 - 21.00' => '20:00',
                        '21.00 - 22.00' => '21:00',
                        '22.00 - 23.00' => '22:00',
                    ],
                    'malam' => [
                        '23.00 - 24.00' => '23:00',
                        '24.00 - 01.00' => '00:00',
                        '01.00 - 02.00' => '01:00',
                        '02.00 - 03.00' => '02:00',
                        '03.00 - 04.00' => '03:00',
                        '04.00 - 05.00' => '04:00',
                        '05.00 - 06.00' => '05:00',
                        '06.00 - 07.00' => '06:00',
                    ],
                ];

                // Hitung target per shift dari planning jika tidak ada di planning_produksi_jam
                $targetShiftPagi = $planning->target_shift_pagi ?? 0;
                $targetShiftSiang = $planning->target_shift_siang ?? 0;
                $targetShiftMalam = $planning->target_shift_malam ?? 0;

                // Jika target shift tidak ada, bagi total planning per 3 shift
                if ($targetShiftPagi == 0 && $targetShiftSiang == 0 && $targetShiftMalam == 0) {
                    $targetPerShift = round(($planning->qty_planning_produksi ?? 0) / 3);
                    $targetShiftPagi = $targetPerShift;
                    $targetShiftSiang = $targetPerShift;
                    $targetShiftMalam = $targetPerShift;
                }

                // Target per jam jika tidak ada di planning_produksi_jam
                // Gunakan floor untuk menghindari pembulatan ke atas yang menyebabkan total melebihi target shift
                // Target akan didistribusikan secara proporsional dengan memastikan total tetap sama dengan target shift
                $targetPerJamPagi = $targetShiftPagi > 0 ? floor($targetShiftPagi / 8) : 0;
                $targetPerJamSiang = $targetShiftSiang > 0 ? floor($targetShiftSiang / 8) : 0;
                $targetPerJamMalam = $targetShiftMalam > 0 ? floor($targetShiftMalam / 8) : 0;

                // Hitung sisa target yang belum terdistribusi (untuk distribusi ke jam-jam tertentu)
                $sisaTargetPagi = $targetShiftPagi - ($targetPerJamPagi * 8);
                $sisaTargetSiang = $targetShiftSiang - ($targetPerJamSiang * 8);
                $sisaTargetMalam = $targetShiftMalam - ($targetPerJamMalam * 8);

                Log::info('PDF Single - Produksi per jam data', [
                    'planning_id' => $planning->id,
                    'total_records' => $produksiPerJam->count(),
                    'planning_jam_count' => $planningJam->count(),
                    'target_shift' => [
                        'pagi' => $targetShiftPagi,
                        'siang' => $targetShiftSiang,
                        'malam' => $targetShiftMalam,
                    ],
                    'sample_data' => $produksiPerJam->take(3)->map(function ($item) {
                        return [
                            'shift' => $item->shift,
                            'jam' => $item->jam,
                            'ok_count' => $item->ok_count,
                            'ng_part' => $item->ng_part,
                            'target' => $item->target
                        ];
                    })
                ]);

                // Format data per shift dan jam untuk view
                $produksiPerJamData = [
                    'pagi' => [],
                    'siang' => [],
                    'malam' => []
                ];

                // Hitung total per shift untuk summary
                $totalPerShift = [
                    'pagi' => ['target' => 0, 'ok' => 0, 'ng' => 0],
                    'siang' => ['target' => 0, 'ok' => 0, 'ng' => 0],
                    'malam' => ['target' => 0, 'ok' => 0, 'ng' => 0]
                ];

                // Daftar semua jam yang harus ditampilkan
                $allJamSlots = [
                    'pagi' => [
                        '07.00 - 08.00',
                        '08.00 - 09.00',
                        '09.00 - 10.00',
                        '10.00 - 11.00',
                        '11.00 - 12.00',
                        '12.00 - 13.00',
                        '13.00 - 14.00',
                        '14.00 - 15.00',
                    ],
                    'siang' => [
                        '15.00 - 16.00',
                        '16.00 - 17.00',
                        '17.00 - 18.00',
                        '18.00 - 19.00',
                        '19.00 - 20.00',
                        '20.00 - 21.00',
                        '21.00 - 22.00',
                        '22.00 - 23.00',
                    ],
                    'malam' => [
                        '23.00 - 24.00',
                        '24.00 - 01.00',
                        '01.00 - 02.00',
                        '02.00 - 03.00',
                        '03.00 - 04.00',
                        '04.00 - 05.00',
                        '05.00 - 06.00',
                        '06.00 - 07.00',
                    ],
                ];

                // Kelompokkan data produksi per jam berdasarkan shift dan jam untuk menggabungkan multiple operator
                $groupedProduksiPerJam = [];
                foreach ($produksiPerJam as $item) {
                    $key = $item->shift . '_' . $item->jam;
                    if (!isset($groupedProduksiPerJam[$key])) {
                        $groupedProduksiPerJam[$key] = [];
                    }
                    $groupedProduksiPerJam[$key][] = $item;
                }

                // Proses data produksi per jam yang sudah dikelompokkan
                foreach ($groupedProduksiPerJam as $key => $items) {
                    // Ambil shift dan jam dari key
                    $firstItem = $items[0];
                    $shift = $firstItem->shift;
                    $jam = $firstItem->jam;

                    // Cari target dari planning_produksi_jam (prioritas utama)
                    $targetFromPlanning = null;
                    if (isset($jamMapping[$shift][$jam])) {
                        $jamKey = $jamMapping[$shift][$jam];
                        $planningJamKey = $shift . '_' . $jamKey;
                        if ($planningJam->has($planningJamKey)) {
                            $targetFromPlanning = $planningJam[$planningJamKey]->target;
                        }
                    }

                    // Gunakan target dari planning_produksi_jam jika ada
                    // Jika tidak ada, gunakan dari produksi_per_jam (ambil dari record pertama)
                    // Jika masih 0, gunakan target per jam default berdasarkan shift
                    $target = $targetFromPlanning;
                    if ($target === null || $target == 0) {
                        $target = $firstItem->target;
                        if ($target == 0) {
                            // Jika masih 0, gunakan target per jam default berdasarkan shift
                            // Tentukan index jam untuk distribusi sisa target
                            $jamIndex = array_search($jam, $allJamSlots[$shift] ?? []);
                            $jamIndex = $jamIndex !== false ? $jamIndex : 0;

                            switch ($shift) {
                                case 'pagi':
                                    $target = $targetPerJamPagi;
                                    // Tambahkan sisa target ke jam-jam awal shift
                                    if ($sisaTargetPagi > 0 && $jamIndex < $sisaTargetPagi) {
                                        $target += 1;
                                    }
                                    break;
                                case 'siang':
                                    $target = $targetPerJamSiang;
                                    if ($sisaTargetSiang > 0 && $jamIndex < $sisaTargetSiang) {
                                        $target += 1;
                                    }
                                    break;
                                case 'malam':
                                    $target = $targetPerJamMalam;
                                    if ($sisaTargetMalam > 0 && $jamIndex < $sisaTargetMalam) {
                                        $target += 1;
                                    }
                                    break;
                            }
                        }
                    }

                    // Gabungkan data dari semua record untuk shift dan jam yang sama
                    $totalOkCount = 0;
                    $totalNgPart = 0;
                    $operatorCodes = [];
                    $totalNgSubpart = [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                        7 => 0
                    ];
                    $masalah = '';
                    $tindakan = '';
                    $checkerPrd = '';
                    $checkerQc = '';

                    foreach ($items as $item) {
                        // Data OK dan NG dari produksi_per_jam (realtime dari input)
                        // Pastikan ok_count dan ng_part adalah integer, bukan string atau null
                        // Gunakan getRawOriginal untuk mendapatkan nilai mentah dari database
                        $okCountRaw = $item->getRawOriginal('ok_count');
                        $ngPartRaw = $item->getRawOriginal('ng_part');
                        $operatorCodeRaw = $item->getRawOriginal('operator_code');

                        // Cast ke tipe yang benar
                        $okCount = is_numeric($okCountRaw) ? (int)$okCountRaw : 0;
                        $ngPart = is_numeric($ngPartRaw) ? (int)$ngPartRaw : 0;
                        $operatorCode = is_string($operatorCodeRaw) ? trim($operatorCodeRaw) : '';

                        // Akumulasi OK dan NG
                        $totalOkCount += $okCount;
                        $totalNgPart += $ngPart;

                        // Kumpulkan operator code (jika ada dan belum ada di array)
                        if (!empty($operatorCode) && !in_array($operatorCode, $operatorCodes)) {
                            $operatorCodes[] = $operatorCode;
                        }

                        // Akumulasi NG subpart
                        for ($i = 1; $i <= 7; $i++) {
                            $ngSubpartRaw = $item->getRawOriginal('ng_subpart_' . $i);
                            $ngSubpart = is_numeric($ngSubpartRaw) ? (int)$ngSubpartRaw : 0;
                            $totalNgSubpart[$i] += $ngSubpart;
                        }

                        // Ambil masalah, tindakan, checker dari record yang memiliki data (prioritas record terakhir yang memiliki data)
                        if (!empty($item->masalah)) {
                            $masalah = (string)$item->masalah;
                        }
                        if (!empty($item->tindakan)) {
                            $tindakan = (string)$item->tindakan;
                        }
                        if (!empty($item->checker_prd)) {
                            $checkerPrd = (string)$item->checker_prd;
                        }
                        if (!empty($item->checker_qc)) {
                            $checkerQc = (string)$item->checker_qc;
                        }
                    }

                    // Gabungkan operator codes dengan "/" (seperti di contoh: "C57/C56" atau "057/C56")
                    // Jika hanya satu operator, tampilkan tanpa separator
                    $operatorCodeCombined = count($operatorCodes) > 1 ? implode('/', $operatorCodes) : (count($operatorCodes) == 1 ? $operatorCodes[0] : '');

                    // Debug log untuk setiap kelompok yang memiliki data
                    if ($totalOkCount > 0 || $totalNgPart > 0 || !empty($operatorCodeCombined)) {
                        Log::info('PDF Single - Processing grouped produksi_per_jam', [
                            'shift' => $shift,
                            'jam' => $jam,
                            'records_count' => count($items),
                            'total_ok_count' => $totalOkCount,
                            'total_ng_part' => $totalNgPart,
                            'operator_codes' => $operatorCodes,
                            'operator_code_combined' => $operatorCodeCombined,
                        ]);
                    }

                    $produksiPerJamData[$shift][$jam] = [
                        'target' => (int)$target,
                        'ok_count' => $totalOkCount,
                        'ng_part' => $totalNgPart,
                        'operator_code' => $operatorCodeCombined,
                        'ng_subpart_1' => $totalNgSubpart[1],
                        'ng_subpart_2' => $totalNgSubpart[2],
                        'ng_subpart_3' => $totalNgSubpart[3],
                        'ng_subpart_4' => $totalNgSubpart[4],
                        'ng_subpart_5' => $totalNgSubpart[5],
                        'ng_subpart_6' => $totalNgSubpart[6],
                        'ng_subpart_7' => $totalNgSubpart[7],
                        'masalah' => $masalah,
                        'tindakan' => $tindakan,
                        'checker_prd' => $checkerPrd,
                        'checker_qc' => $checkerQc,
                    ];

                    // Akumulasi total per shift (target tidak diakumulasi karena akan diganti dengan target shift)
                    $totalPerShift[$shift]['ok'] += $totalOkCount;
                    $totalPerShift[$shift]['ng'] += $totalNgPart;
                }

                // Isi jam yang belum ada input produksi dengan target dari planning
                foreach ($allJamSlots as $shift => $jamSlots) {
                    foreach ($jamSlots as $jamSlot) {
                        if (!isset($produksiPerJamData[$shift][$jamSlot])) {
                            // Cari target dari planning_produksi_jam (prioritas utama)
                            $targetFromPlanning = null;
                            if (isset($jamMapping[$shift][$jamSlot])) {
                                $jamKey = $jamMapping[$shift][$jamSlot];
                                $planningJamKey = $shift . '_' . $jamKey;
                                if ($planningJam->has($planningJamKey)) {
                                    $targetFromPlanning = $planningJam[$planningJamKey]->target;
                                }
                            }

                            // Gunakan target dari planning_produksi_jam jika ada
                            // Jika tidak, gunakan target per jam default berdasarkan shift
                            // Distribusikan sisa target ke jam-jam awal shift
                            $target = $targetFromPlanning;
                            if ($target === null || $target == 0) {
                                // Tentukan sisa target dan target per jam untuk shift ini
                                $sisaTarget = 0;
                                $targetPerJam = 0;
                                switch ($shift) {
                                    case 'pagi':
                                        $sisaTarget = $sisaTargetPagi;
                                        $targetPerJam = $targetPerJamPagi;
                                        break;
                                    case 'siang':
                                        $sisaTarget = $sisaTargetSiang;
                                        $targetPerJam = $targetPerJamSiang;
                                        break;
                                    case 'malam':
                                        $sisaTarget = $sisaTargetMalam;
                                        $targetPerJam = $targetPerJamMalam;
                                        break;
                                }

                                $target = $targetPerJam;
                                // Tentukan index jam untuk distribusi sisa target
                                $jamIndex = array_search($jamSlot, $jamSlots);
                                if ($jamIndex !== false && $sisaTarget > 0 && $jamIndex < $sisaTarget) {
                                    $target += 1;
                                }
                            }

                            $produksiPerJamData[$shift][$jamSlot] = [
                                'target' => (int)$target,
                                'ok_count' => 0,
                                'ng_part' => 0,
                                'operator_code' => '',
                                'ng_subpart_1' => 0,
                                'ng_subpart_2' => 0,
                                'ng_subpart_3' => 0,
                                'ng_subpart_4' => 0,
                                'ng_subpart_5' => 0,
                                'ng_subpart_6' => 0,
                                'ng_subpart_7' => 0,
                                'masalah' => '',
                                'tindakan' => '',
                                'checker_prd' => '',
                                'checker_qc' => '',
                            ];

                            // Target tidak diakumulasi karena akan diganti dengan target shift dari planning
                        }
                    }
                }

                // Hitung grand total - gunakan target shift dari planning untuk summary
                // Target per shift HARUS sesuai dengan planning, bukan dari akumulasi per jam
                $totalPerShift['pagi']['target'] = $targetShiftPagi;
                $totalPerShift['siang']['target'] = $targetShiftSiang;
                $totalPerShift['malam']['target'] = $targetShiftMalam;

                // Ambil total OK dan NG dari InputProduksi untuk planning ini (realtime)
                // Ini adalah sumber data utama untuk OK/NG
                $totalOkFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)
                    ->sum('ok_count');
                $totalNgFromInput = \App\Models\InputProduksi::where('planning_id', $planning->id)
                    ->sum('ng_count');

                // Hitung total OK dan NG dari produksi_per_jam yang sudah diakumulasi
                $totalOkFromProduksiPerJam = $totalPerShift['pagi']['ok'] + $totalPerShift['siang']['ok'] + $totalPerShift['malam']['ok'];
                $totalNgFromProduksiPerJam = $totalPerShift['pagi']['ng'] + $totalPerShift['siang']['ng'] + $totalPerShift['malam']['ng'];

                // Jika ada perbedaan antara InputProduksi dan produksi_per_jam, sesuaikan
                // Prioritas: Gunakan data dari produksi_per_jam jika ada, jika tidak gunakan InputProduksi
                if ($totalOkFromProduksiPerJam == 0 && $totalNgFromProduksiPerJam == 0) {
                    // Jika tidak ada data di produksi_per_jam, bagi proporsional berdasarkan target shift
                    $totalTarget = $targetShiftPagi + $targetShiftSiang + $targetShiftMalam;
                    if ($totalTarget > 0) {
                        $totalPerShift['pagi']['ok'] = round(($targetShiftPagi / $totalTarget) * $totalOkFromInput);
                        $totalPerShift['pagi']['ng'] = round(($targetShiftPagi / $totalTarget) * $totalNgFromInput);
                        $totalPerShift['siang']['ok'] = round(($targetShiftSiang / $totalTarget) * $totalOkFromInput);
                        $totalPerShift['siang']['ng'] = round(($targetShiftSiang / $totalTarget) * $totalNgFromInput);
                        $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                        $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                    }
                } else {
                    // Jika ada data di produksi_per_jam, gunakan itu tapi pastikan total sesuai dengan InputProduksi
                    // Jika ada perbedaan, sesuaikan proporsional
                    if ($totalOkFromInput > 0 || $totalNgFromInput > 0) {
                        $totalOkNgFromProduksiPerJam = $totalOkFromProduksiPerJam + $totalNgFromProduksiPerJam;
                        $totalOkNgFromInput = $totalOkFromInput + $totalNgFromInput;

                        if ($totalOkNgFromProduksiPerJam > 0 && abs($totalOkNgFromProduksiPerJam - $totalOkNgFromInput) > 0.1) {
                            // Ada perbedaan, sesuaikan proporsional
                            $ratio = $totalOkNgFromInput / $totalOkNgFromProduksiPerJam;
                            $totalPerShift['pagi']['ok'] = round($totalPerShift['pagi']['ok'] * $ratio);
                            $totalPerShift['pagi']['ng'] = round($totalPerShift['pagi']['ng'] * $ratio);
                            $totalPerShift['siang']['ok'] = round($totalPerShift['siang']['ok'] * $ratio);
                            $totalPerShift['siang']['ng'] = round($totalPerShift['siang']['ng'] * $ratio);
                            $totalPerShift['malam']['ok'] = $totalOkFromInput - $totalPerShift['pagi']['ok'] - $totalPerShift['siang']['ok'];
                            $totalPerShift['malam']['ng'] = $totalNgFromInput - $totalPerShift['pagi']['ng'] - $totalPerShift['siang']['ng'];
                        }
                    }
                }

                // Hitung grand total - pastikan sesuai dengan planning dan InputProduksi
                $grandTotal = [
                    'target' => $targetShiftPagi + $targetShiftSiang + $targetShiftMalam,
                    'ok' => $totalOkFromInput, // Gunakan langsung dari InputProduksi
                    'ng' => $totalNgFromInput   // Gunakan langsung dari InputProduksi
                ];

                // Prepare Persiapan Produksi data
                $persiapanData = [];
                if ($data->ploting && $data->ploting->persiapanChecks) {
                    $persiapanData = $data->ploting->persiapanChecks->groupBy(function ($check) {
                        // Extract shift from item_name (e.g., "Briefing (Pagi)" -> "Pagi")
                        if (preg_match('/\((\w+)\)$/', $check->item_name, $matches)) {
                            return ucfirst(strtolower($matches[1]));
                        }
                        return 'Unknown';
                    })->map(function ($checks, $shift) {
                        return $checks->map(function ($check) {
                            // Remove shift suffix from item_name
                            $itemName = preg_replace('/\s*\([^)]+\)$/', '', $check->item_name);
                            return [
                                'item_name' => $itemName,
                                'operator_checked' => $check->operator_checked,
                                'leader_checked' => $check->leader_checked,
                                'keterangan' => $check->keterangan
                            ];
                        })->toArray();
                    })->toArray();
                }

                // Prepare NG Part Types data
                $ngPartTypesData = [];
                try {
                    $allNgPartTypes = \App\Models\NgPartType::active()->ordered()->get();

                    // Get NG part types from input_produksi
                    $ngPartTypesByShift = [
                        'Shift 1' => [],
                        'Shift 2' => [],
                        'Shift 3' => []
                    ];

                    if ($data->ngPartTypes && $data->ngPartTypes->count() > 0) {
                        foreach ($data->ngPartTypes as $ngPart) {
                            $quantity = $ngPart->pivot->quantity ?? 0;
                            if ($quantity > 0) {
                                // For now, distribute to all shifts
                                // You can refine based on actual shift data
                                foreach (['Shift 1', 'Shift 2', 'Shift 3'] as $shift) {
                                    $ngPartTypesByShift[$shift][] = [
                                        'name' => $ngPart->name,
                                        'code' => $ngPart->code,
                                        'quantity' => $quantity
                                    ];
                                }
                            }
                        }
                    }

                    $ngPartTypesData = [
                        'all_types' => $allNgPartTypes,
                        'by_shift' => $ngPartTypesByShift
                    ];
                } catch (\Exception $e) {
                    Log::warning('Error loading NG Part Types: ' . $e->getMessage());
                    $ngPartTypesData = [
                        'all_types' => collect([]),
                        'by_shift' => [
                            'Shift 1' => [],
                            'Shift 2' => [],
                            'Shift 3' => []
                        ]
                    ];
                }

                // Increase memory limit for PDF generation
                ini_set('memory_limit', '1024M');

                $options = [
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'isRemoteEnabled' => false,
                    'chroot' => [public_path(), storage_path()],
                ];

                $pdf = Pdf::loadView('laporan-produksi.cetak-single', compact(
                    'data',
                    'logoBase64',
                    'operatorsByShift',
                    'operatorsQrByShift',
                    'leadersByShift',
                    'leadersQrByShift',
                    'produksiPerJamData',
                    'totalPerShift',
                    'grandTotal',
                    'persiapanData',
                    'ngPartTypesData'
                ));

                // Clean up to free memory
                gc_collect_cycles();

                $pdf->setPaper('a4', 'portrait');
                $pdf->setOptions($options);

                return $pdf->stream('laporan-produksi-' . $data->id . '-' . date('Y-m-d') . '.pdf');
            } catch (\Exception $pdfError) {
                Log::error('PDF Single Stream Error: ' . $pdfError->getMessage());
                Log::error('PDF Single Stream Trace: ' . $pdfError->getTraceAsString());
                // Re-throw to be caught by outer catch
                throw $pdfError;
            }
        } catch (\Exception $e) {
            Log::error('PDF Single Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            // Redirect back with error message
            return redirect()->route('laporan-produksi.index')
                ->with('error', 'Gagal menghasilkan PDF: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk load CSS file dengan error handling
     */
    private function loadCssFile($path)
    {
        try {
            $fullPath = public_path($path);
            if (file_exists($fullPath) && is_readable($fullPath)) {
                $content = @file_get_contents($fullPath);
                if ($content === false) {
                    Log::warning('Failed to read CSS file: ' . $fullPath);
                    return '';
                }
                // Remove BOM and other problematic characters
                $content = preg_replace('/\xEF\xBB\xBF/', '', $content);
                // Remove any null bytes
                $content = str_replace("\0", '', $content);
                // Remove any PHP tags that might have been accidentally included
                $content = preg_replace('/<\?php.*?\?>/is', '', $content);
                $content = preg_replace('/<\?.*?\?>/is', '', $content);
                // Remove any control characters except newlines and tabs
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
                // Ensure content ends properly - remove trailing whitespace and newlines
                $content = rtrim($content);
                // Ensure content doesn't end with incomplete CSS
                $content = rtrim($content, " \t\n\r\0\x0B");
                // Validate that content is not empty after cleaning
                if (empty($content)) {
                    Log::warning('CSS file is empty after cleaning: ' . $fullPath);
                    return '';
                }
                // Ensure content doesn't have unclosed braces (basic check)
                $openBraces = substr_count($content, '{');
                $closeBraces = substr_count($content, '}');
                if ($openBraces !== $closeBraces) {
                    Log::warning('CSS file has mismatched braces: ' . $fullPath . ' (open: ' . $openBraces . ', close: ' . $closeBraces . ')');
                    // Don't return empty, just log the warning
                }
                // Remove any potential problematic characters that could break Blade parsing
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
                return $content;
            }
            Log::warning('CSS file not found or not readable: ' . $fullPath);
            return '';
        } catch (\Exception $e) {
            Log::warning('Failed to load CSS file: ' . $path . ' - ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Helper method untuk mendapatkan path QR code
     */
    private function getQrCodePath($qrPath)
    {
        if (!$qrPath) return null;
        $fullPath = storage_path('app/public/' . $qrPath);
        return (file_exists($fullPath) && is_readable($fullPath)) ? $fullPath : null;
    }

    /**
     * Helper method untuk mendapatkan path logo
     */
    private function getLogoPath()
    {
        $logoPath = public_path('images/logo.png');
        return (file_exists($logoPath) && is_readable($logoPath)) ? $logoPath : null;
    }

    /**
     * Helper method untuk convert QR code path ke base64 (KEEP FOR COMPATIBILITY)
     */
    private function getQrCodeBase64($qrPath)
    {
        if (!$qrPath) return null;
        try {
            $fullPath = storage_path('app/public/' . $qrPath);
            if (file_exists($fullPath) && is_readable($fullPath)) {
                $qrData = @file_get_contents($fullPath);
                if ($qrData !== false && !empty($qrData)) {
                    return 'data:image/png;base64,' . base64_encode($qrData);
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method untuk convert logo ke base64 (KEEP FOR COMPATIBILITY)
     */
    private function getLogoBase64()
    {
        try {
            $logoPath = public_path('images/logo.png');
            if (file_exists($logoPath) && is_readable($logoPath)) {
                $logoData = @file_get_contents($logoPath);
                if ($logoData !== false && !empty($logoData)) {
                    return 'data:image/png;base64,' . base64_encode($logoData);
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate QR code dari TTD.png untuk tanda tangan
     * QR code akan berisi URL ke TTD.png atau data verifikasi
     */
    private function generateTtdQrCode($inputId, $type = 'operator', $userData = null)
    {
        try {
            // URL ke TTD.png
            $ttdUrl = route('secure.signature');

            // Data untuk QR code (bisa ditambahkan informasi tambahan)
            $qrData = [
                'type' => strtoupper($type) . '_SIGNATURE_TTD',
                'input_produksi_id' => $inputId,
                'ttd_url' => $ttdUrl,
                'timestamp' => now()->toISOString(),
            ];

            // Tambahkan data user jika ada
            if ($userData) {
                $qrData['user_id'] = $userData->id ?? null;
                $qrData['user_nama'] = $userData->nama ?? null;
                $qrData['user_nik'] = $userData->nik ?? null;
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

            $filename = 'signature_qr/' . $inputId . '_' . $type . '_ttd_' . time() . '.png';
            $path = 'public/' . $filename;

            // Get binary data
            $qrDataBinary = $result->getString();
            Storage::put($path, $qrDataBinary);

            Log::info('TTD QR code generated', [
                'input_id' => $inputId,
                'type' => $type,
                'filename' => $filename
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to generate TTD QR code: ' . $e->getMessage(), [
                'input_id' => $inputId,
                'type' => $type,
                'error' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Helper method untuk mendapatkan TTD QR code path
     */
    private function getTtdQrCodePath($inputId, $type = 'operator', $userData = null)
    {
        try {
            $input = InputProduksi::find($inputId);
            if (!$input) return null;

            $qrPath = ($type === 'operator') ? $input->operator_signature_qr : $input->leader_signature_qr;

            if ($qrPath) {
                return $this->getQrCodePath($qrPath);
            }

            $newQrPath = $this->generateTtdQrCode($inputId, $type, $userData);
            if ($newQrPath) {
                if ($type === 'operator') {
                    $input->update(['operator_signature_qr' => $newQrPath]);
                } else {
                    $input->update(['leader_signature_qr' => $newQrPath]);
                }
                return $this->getQrCodePath($newQrPath);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method untuk mendapatkan TTD QR code base64 (KEEP FOR COMPATIBILITY)
     */
    private function getTtdQrCodeBase64($inputId, $type = 'operator', $userData = null)
    {
        try {
            $input = InputProduksi::find($inputId);
            if (!$input) return null;
            $qrPath = ($type === 'operator') ? $input->operator_signature_qr : $input->leader_signature_qr;
            if ($qrPath) return $this->getQrCodeBase64($qrPath);
            $newQrPath = $this->generateTtdQrCode($inputId, $type, $userData);
            if ($newQrPath) {
                if ($type === 'operator') $input->update(['operator_signature_qr' => $newQrPath]);
                else $input->update(['leader_signature_qr' => $newQrPath]);
                return $this->getQrCodeBase64($newQrPath);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get leader berdasarkan shift dan grup dari input produksi
     */
    private function getLeaderByShiftAndGrup($inputProduksi)
    {
        try {
            // Cek apakah ada approver yang sudah approve
            if ($inputProduksi->approver) {
                return $inputProduksi->approver;
            }

            // Ambil dari grup ploting
            $ploting = $inputProduksi->ploting;
            if ($ploting && $ploting->grup && $ploting->grup->leader) {
                return $ploting->grup->leader;
            }

            // Ambil dari planning plotings (ambil leader dari grup pertama)
            $planning = $inputProduksi->planning;
            if ($planning && $planning->plotings) {
                foreach ($planning->plotings as $plot) {
                    if ($plot->grup && $plot->grup->leader) {
                        return $plot->grup->leader;
                    }
                }
            }

            // Fallback: cari leader berdasarkan shift dari planning
            if ($planning && $planning->shift) {
                $shift = $this->normalizeShift($planning->shift);
                $grup = \App\Models\Grup::where('shift', $shift)
                    ->orWhere('shift', 'all')
                    ->whereHas('leader')
                    ->first();

                if ($grup && $grup->leader) {
                    return $grup->leader;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get leader by shift and grup: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get leaders grouped by shift (1, 2, 3)
     */
    private function getLeadersByShift($dataProduksi)
    {
        $leadersByShift = [
            'shift_1' => null,
            'shift_2' => null,
            'shift_3' => null,
        ];

        try {
            // Group data by shift
            $shiftGroups = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => [],
            ];

            foreach ($dataProduksi as $item) {
                $planning = $item->planning;
                if (!$planning) continue;

                $shift = $this->normalizeShift($planning->shift);

                // Tentukan shift berdasarkan planning shift atau waktu
                if ($shift === 'pagi' || $shift === '1') {
                    $shiftGroups['shift_1'][] = $item;
                } elseif ($shift === 'siang' || $shift === '2') {
                    $shiftGroups['shift_2'][] = $item;
                } elseif ($shift === 'malam' || $shift === '3') {
                    $shiftGroups['shift_3'][] = $item;
                } else {
                    // Jika shift tidak jelas, coba dari waktu created_at
                    $hour = $item->created_at->hour;
                    if ($hour >= 7 && $hour < 15) {
                        $shiftGroups['shift_1'][] = $item;
                    } elseif ($hour >= 15 && $hour < 23) {
                        $shiftGroups['shift_2'][] = $item;
                    } else {
                        $shiftGroups['shift_3'][] = $item;
                    }
                }
            }

            // Ambil leader untuk setiap shift
            foreach ($shiftGroups as $shiftKey => $items) {
                if (!empty($items)) {
                    // Ambil leader dari item pertama di shift tersebut
                    $firstItem = $items[0];
                    $leader = $this->getLeaderByShiftAndGrup($firstItem);
                    if ($leader) {
                        $leadersByShift[$shiftKey] = $leader;
                    }
                }
            }

            return $leadersByShift;
        } catch (\Exception $e) {
            Log::error('Failed to get leaders by shift: ' . $e->getMessage());
            return $leadersByShift;
        }
    }

    /**
     * Normalize shift value
     */
    private function normalizeShift($shift)
    {
        if (!$shift) return null;

        $shift = strtolower(trim($shift));

        // Mapping shift values
        if (in_array($shift, ['pagi', '1', 'shift_1', 'shift 1', 'shift1'])) {
            return 'pagi';
        } elseif (in_array($shift, ['siang', '2', 'shift_2', 'shift 2', 'shift2'])) {
            return 'siang';
        } elseif (in_array($shift, ['malam', '3', 'shift_3', 'shift 3', 'shift3'])) {
            return 'malam';
        }

        return $shift;
    }

    /**
     * Distribusikan data OK/NG dari InputProduksi ke produksi_per_jam berdasarkan waktu input
     * Fungsi ini memastikan data OK/NG muncul di jam yang sesuai dengan waktu input
     * 
     * STRATEGI:
     * 1. Ambil semua data produksi_per_jam yang sudah ada (yang sudah tersimpan melalui saveProduksiPerJamRealtime)
     * 2. Hitung total OK/NG dari produksi_per_jam per shift
     * 3. Bandingkan dengan total OK/NG dari InputProduksi
     * 4. Jika ada selisih, distribusikan ke jam-jam yang sesuai berdasarkan waktu input
     */
    private function distributeInputProduksiToProduksiPerJam($planningId)
    {
        try {
            // Ambil semua InputProduksi untuk planning ini
            $inputProduksi = \App\Models\InputProduksi::where('planning_id', $planningId)
                ->with('ploting.operator')
                ->get();

            if ($inputProduksi->isEmpty()) {
                return;
            }

            // Hitung total OK/NG dari InputProduksi
            $totalOkFromInput = $inputProduksi->sum('ok_count');
            $totalNgFromInput = $inputProduksi->sum('ng_count');

            // Hitung total OK/NG dari produksi_per_jam yang sudah ada
            $totalOkFromProduksiPerJam = \App\Models\ProduksiPerJam::where('planning_id', $planningId)
                ->sum('ok_count');
            $totalNgFromProduksiPerJam = \App\Models\ProduksiPerJam::where('planning_id', $planningId)
                ->sum('ng_part');

            // Jika total di produksi_per_jam sudah sama atau lebih besar dari InputProduksi, tidak perlu distribusi
            // Ini berarti semua data sudah tersimpan secara realtime melalui saveProduksiPerJamRealtime
            // ketika tombol OK/NG diklik di input-produksi/index.blade.php
            // Setiap klik OK/NG akan langsung menambah ok_count atau ng_part di produksi_per_jam untuk jam yang sesuai
            if ($totalOkFromProduksiPerJam >= $totalOkFromInput && $totalNgFromProduksiPerJam >= $totalNgFromInput) {
                Log::info('Data produksi_per_jam sudah lengkap (semua data tersimpan realtime)', [
                    'planning_id' => $planningId,
                    'ok_from_input' => $totalOkFromInput,
                    'ok_from_produksi_per_jam' => $totalOkFromProduksiPerJam,
                    'ng_from_input' => $totalNgFromInput,
                    'ng_from_produksi_per_jam' => $totalNgFromProduksiPerJam,
                    'message' => 'Semua data OK/NG sudah tersimpan secara realtime melalui saveProduksiPerJamRealtime ketika tombol diklik. Data akan muncul di PDF sesuai dengan jam input yang sebenarnya.'
                ]);
                return;
            }

            // Jika ada selisih kecil (mungkin karena rounding atau timing), log sebagai info tapi tetap lanjutkan distribusi
            $okDiff = abs($totalOkFromProduksiPerJam - $totalOkFromInput);
            $ngDiff = abs($totalNgFromProduksiPerJam - $totalNgFromInput);
            if ($okDiff <= 1 && $ngDiff <= 1 && $totalOkFromProduksiPerJam > 0) {
                Log::info('Selisih kecil antara InputProduksi dan produksi_per_jam (mungkin karena timing)', [
                    'planning_id' => $planningId,
                    'ok_diff' => $okDiff,
                    'ng_diff' => $ngDiff,
                    'message' => 'Selisih kecil, kemungkinan besar data sudah lengkap. Tetap lanjutkan distribusi untuk memastikan.'
                ]);
            }

            // Mapping jam dari format waktu ke format "HH.00 - HH.00"
            // Format harus sama persis dengan getCurrentJam() di InputProduksiController
            $getJamFromTime = function ($hour) {
                $jamAwal = $hour;
                $jamAkhir = $hour + 1;

                // Handle shift malam yang melintasi hari
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
            };

            // Tentukan shift dari jam
            $getShiftFromHour = function ($hour) {
                if ($hour >= 7 && $hour < 15) {
                    return 'pagi';
                } elseif ($hour >= 15 && $hour < 23) {
                    return 'siang';
                } else {
                    return 'malam';
                }
            };

            // Ambil semua produksi_per_jam yang sudah ada
            $existingProduksiPerJam = \App\Models\ProduksiPerJam::where('planning_id', $planningId)
                ->get()
                ->keyBy(function ($item) {
                    return $item->shift . '_' . $item->jam;
                });

            // Kelompokkan InputProduksi berdasarkan jam input untuk distribusi
            $inputByHour = [];
            foreach ($inputProduksi as $input) {
                // Ambil waktu input (gunakan updated_at karena itu waktu terakhir input)
                $inputTime = $input->updated_at ?? $input->created_at;
                if (!$inputTime) {
                    continue;
                }

                $hour = (int) $inputTime->format('H');
                $shift = $getShiftFromHour($hour);
                $jam = $getJamFromTime($hour);

                $key = $shift . '_' . $jam;

                if (!isset($inputByHour[$key])) {
                    $inputByHour[$key] = [
                        'shift' => $shift,
                        'jam' => $jam,
                        'ok_count' => 0,
                        'ng_count' => 0,
                        'input_produksi_id' => $input->id,
                        'ploting_id' => $input->ploting_id,
                        'operator' => $input->ploting->operator ?? null,
                    ];
                }

                // Akumulasi OK/NG untuk jam ini
                // Catatan: InputProduksi menyimpan total, jadi kita bagi rata atau gunakan proporsi
                // Untuk sementara, kita asumsikan setiap InputProduksi adalah 1 unit per jam
                // Tapi kita perlu cara yang lebih baik untuk mendistribusikan
            }

            // Distribusikan sisa OK/NG yang belum terdistribusi
            // Prioritas: Gunakan data dari produksi_per_jam yang sudah ada (yang dibuat melalui saveProduksiPerJamRealtime)
            // Jika masih ada sisa, distribusikan berdasarkan waktu input yang sebenarnya dari InputProduksi
            $remainingOk = max(0, $totalOkFromInput - $totalOkFromProduksiPerJam);
            $remainingNg = max(0, $totalNgFromInput - $totalNgFromProduksiPerJam);

            if ($remainingOk > 0 || $remainingNg > 0) {
                // Strategi: Distribusikan berdasarkan waktu input yang sebenarnya
                // Urutkan InputProduksi berdasarkan waktu input (dari yang paling awal)
                $sortedInputs = $inputProduksi->sortBy(function ($input) {
                    $inputTime = $input->updated_at ?? $input->created_at;
                    return $inputTime ? $inputTime->timestamp : 0;
                });

                // Daftar jam untuk setiap shift (dari awal ke akhir)
                $jamSlotsByShift = [
                    'pagi' => [
                        '07.00 - 08.00',
                        '08.00 - 09.00',
                        '09.00 - 10.00',
                        '10.00 - 11.00',
                        '11.00 - 12.00',
                        '12.00 - 13.00',
                        '13.00 - 14.00',
                        '14.00 - 15.00',
                    ],
                    'siang' => [
                        '15.00 - 16.00',
                        '16.00 - 17.00',
                        '17.00 - 18.00',
                        '18.00 - 19.00',
                        '19.00 - 20.00',
                        '20.00 - 21.00',
                        '21.00 - 22.00',
                        '22.00 - 23.00',
                    ],
                    'malam' => [
                        '23.00 - 24.00',
                        '24.00 - 01.00',
                        '01.00 - 02.00',
                        '02.00 - 03.00',
                        '03.00 - 04.00',
                        '04.00 - 05.00',
                        '05.00 - 06.00',
                        '06.00 - 07.00',
                    ],
                ];

                // Kelompokkan InputProduksi berdasarkan shift dan jam berdasarkan waktu input yang sebenarnya
                // Ini akan memastikan distribusi sesuai dengan waktu input yang ditampilkan di tabel super admin
                $inputByShiftJam = [];
                foreach ($sortedInputs as $input) {
                    $inputTime = $input->updated_at ?? $input->created_at;
                    if (!$inputTime) {
                        continue;
                    }

                    $hour = (int) $inputTime->format('H');
                    $shift = $getShiftFromHour($hour);
                    $jam = $getJamFromTime($hour);

                    $key = $shift . '_' . $jam;
                    if (!isset($inputByShiftJam[$key])) {
                        $inputByShiftJam[$key] = [
                            'shift' => $shift,
                            'jam' => $jam,
                            'inputs' => [],
                            'input_time' => $inputTime,
                        ];
                    }
                    $inputByShiftJam[$key]['inputs'][] = $input;
                }

                // Urutkan berdasarkan waktu input (dari yang paling awal)
                uasort($inputByShiftJam, function ($a, $b) {
                    return $a['input_time']->timestamp <=> $b['input_time']->timestamp;
                });

                // Distribusikan OK/NG ke jam-jam yang sesuai dengan waktu input
                $okDistributed = 0;
                $ngDistributed = 0;

                foreach ($inputByShiftJam as $key => $data) {
                    $shift = $data['shift'];
                    $jam = $data['jam'];
                    $inputs = $data['inputs'];

                    // Cek apakah sudah ada record untuk jam ini
                    $produksiPerJam = $existingProduksiPerJam[$key] ?? null;

                    if (!$produksiPerJam && ($remainingOk > 0 || $remainingNg > 0)) {
                        // Distribusikan OK/NG ke jam ini berdasarkan proporsi
                        // Setiap input akan mendapat 1 unit OK atau NG sesuai dengan proporsi total
                        $okForThisJam = 0;
                        $ngForThisJam = 0;

                        // Hitung proporsi OK/NG untuk jam ini
                        foreach ($inputs as $input) {
                            $inputOk = $input->ok_count ?? 0;
                            $inputNg = $input->ng_count ?? 0;
                            $inputTotal = $inputOk + $inputNg;

                            if ($inputTotal > 0) {
                                // Distribusikan proporsional
                                $okRatio = $inputOk / $inputTotal;
                                $ngRatio = $inputNg / $inputTotal;

                                // Untuk setiap unit, distribusikan ke jam ini
                                // Karena kita tidak tahu unit per klik, kita asumsikan 1 unit per input
                                if ($remainingOk > 0 && $inputOk > 0) {
                                    $okForThisJam += min(1, $remainingOk);
                                    $remainingOk -= min(1, $remainingOk);
                                }
                                if ($remainingNg > 0 && $inputNg > 0) {
                                    $ngForThisJam += min(1, $remainingNg);
                                    $remainingNg -= min(1, $remainingNg);
                                }
                            }
                        }

                        if ($okForThisJam > 0 || $ngForThisJam > 0) {
                            $firstInput = $inputs[0];
                            $user = $firstInput->ploting->operator ?? null;

                            \App\Models\ProduksiPerJam::create([
                                'planning_id' => $planningId,
                                'input_produksi_id' => $firstInput->id,
                                'ploting_id' => $firstInput->ploting_id,
                                'shift' => $shift,
                                'jam' => $jam,
                                'target' => 0,
                                'ok_count' => $okForThisJam,
                                'ng_part' => $ngForThisJam,
                                'operator_code' => $user->nik ?? null,
                            ]);

                            $okDistributed += $okForThisJam;
                            $ngDistributed += $ngForThisJam;

                            Log::info('Record produksi_per_jam dibuat dengan distribusi OK/NG berdasarkan waktu input', [
                                'planning_id' => $planningId,
                                'shift' => $shift,
                                'jam' => $jam,
                                'input_time' => $data['input_time']->format('Y-m-d H:i:s'),
                                'ok_added' => $okForThisJam,
                                'ng_added' => $ngForThisJam,
                                'inputs_count' => count($inputs),
                            ]);
                        }
                    }
                }

                // Jika masih ada sisa setelah distribusi berdasarkan waktu input, distribusikan ke jam-jam awal Shift 1
                // Ini hanya sebagai fallback jika distribusi berdasarkan waktu input tidak mencukupi
                if ($remainingOk > 0 || $remainingNg > 0) {
                    Log::warning('Masih ada sisa OK/NG setelah distribusi berdasarkan waktu input', [
                        'planning_id' => $planningId,
                        'remaining_ok' => $remainingOk,
                        'remaining_ng' => $remainingNg,
                    ]);

                    $jamSlotsPagi = $jamSlotsByShift['pagi'];
                    foreach ($jamSlotsPagi as $jamSlot) {
                        if ($remainingOk <= 0 && $remainingNg <= 0) {
                            break;
                        }

                        $key = 'pagi_' . $jamSlot;
                        $produksiPerJam = $existingProduksiPerJam[$key] ?? null;

                        if (!$produksiPerJam) {
                            $firstInput = $inputProduksi->first();
                            $user = $firstInput->ploting->operator ?? null;

                            $okToAdd = min(1, $remainingOk);
                            $ngToAdd = min(1, $remainingNg);

                            if ($okToAdd > 0 || $ngToAdd > 0) {
                                \App\Models\ProduksiPerJam::create([
                                    'planning_id' => $planningId,
                                    'input_produksi_id' => $firstInput->id,
                                    'ploting_id' => $firstInput->ploting_id,
                                    'shift' => 'pagi',
                                    'jam' => $jamSlot,
                                    'target' => 0,
                                    'ok_count' => $okToAdd,
                                    'ng_part' => $ngToAdd,
                                    'operator_code' => $user->nik ?? null,
                                ]);

                                $remainingOk -= $okToAdd;
                                $remainingNg -= $ngToAdd;
                            }
                        }
                    }
                }
            }

            // Verifikasi distribusi: Ambil semua data produksi_per_jam setelah distribusi
            $finalProduksiPerJam = \App\Models\ProduksiPerJam::where('planning_id', $planningId)
                ->where(function ($q) {
                    $q->where('ok_count', '>', 0)
                        ->orWhere('ng_part', '>', 0);
                })
                ->orderBy('updated_at', 'desc')
                ->get();

            Log::info('Data InputProduksi didistribusikan ke produksi_per_jam', [
                'planning_id' => $planningId,
                'total_input' => $inputProduksi->count(),
                'ok_from_input' => $totalOkFromInput,
                'ok_from_produksi_per_jam' => $totalOkFromProduksiPerJam,
                'ng_from_input' => $totalNgFromInput,
                'ng_from_produksi_per_jam' => $totalNgFromProduksiPerJam,
                'remaining_ok' => $remainingOk,
                'remaining_ng' => $remainingNg,
                'ok_distributed' => $okDistributed,
                'ng_distributed' => $ngDistributed,
                'final_produksi_per_jam' => $finalProduksiPerJam->map(function ($item) {
                    return [
                        'shift' => $item->shift,
                        'jam' => $item->jam,
                        'ok_count' => $item->ok_count,
                        'ng_part' => $item->ng_part,
                        'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error distributing InputProduksi to produksi_per_jam: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
