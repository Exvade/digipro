{{-- ==================== OPERATOR VIEW - CARD LAYOUT ==================== --}}


@if ($plannings->isEmpty())
<div class="row"></div>
<div class="col-12">
    <div class="card card-outline card-primary">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-3 d-block"></i>
            <h4 class="text-muted">Tidak ada tugas produksi</h4>
            <p class="text-muted mb-0">
                Tidak ada tugas produksi yang sedang berjalan untuk Anda saat ini.
            </p>
        </div>
    </div>
</div>
</div>
@else
@php
$now = \Carbon\Carbon::now();
$currentHour = $now->format('H');
$startTime = \Carbon\Carbon::createFromTime($currentHour, 0, 0);
$endTime = \Carbon\Carbon::createFromTime($currentHour + 1, 0, 0);

// Get operator shift from jadwal kerja
$user = Auth::user();
$operatorShift = null;
$operatorShiftSuffix = null;
$jadwalOperator = \App\Models\JadwalKerja::where('user_id', $user->id)
->whereDate('tanggal', \Carbon\Carbon::today())
->where('status', 'aktif')
->first();

if ($jadwalOperator && $jadwalOperator->shift && $jadwalOperator->shift !== 'off') {
$operatorShift = $jadwalOperator->shift;
$operatorShiftSuffix = '(' . ucfirst($operatorShift) . ')';
} else {
// Fallback: determine shift based on current time
$hour = (int) $currentHour;
if ($hour >= 7 && $hour < 15) {
    $operatorShift='pagi' ;
    $operatorShiftSuffix='(Pagi)' ;
    } elseif ($hour>= 15 && $hour < 23) {
        $operatorShift='siang' ;
        $operatorShiftSuffix='(Siang)' ;
        } else {
        $operatorShift='malam' ;
        $operatorShiftSuffix='(Malam)' ;
        }
        }

        // Calculate plannings to open (modal auto-open)
        $planningsToOpen=[];
        if (!empty($plannings)) {
        foreach ($plannings as $planning) {
        $ploting=$planning->plotings->firstWhere('user_id', Auth::id());

        if ($ploting) {
        // Check if checks exist for the OPERATOR'S shift
        $hasShiftChecks = $ploting->persiapanChecks->filter(function ($check) use ($operatorShiftSuffix) {
        return \Illuminate\Support\Str::endsWith($check->item_name, $operatorShiftSuffix) && $check->operator_checked;
        })->isNotEmpty();

        // If no checks for operator's shift, open modal
        if (!$hasShiftChecks) {
        $planningsToOpen[] = $planning->id;
        }
        }
        }
        }
        @endphp

        @foreach ($plannings as $planning)
        @php
        $user = Auth::user();
        $ploting = $planning->plotings->firstWhere('user_id', $user->id);
        $input = $planning->inputProduksi;
        $ok = $input?->ok_count ?? 0;
        $ng = $input?->ng_count ?? 0;

        // Check if persiapan sudah diisi untuk shift operator
        $persiapanCompleted = false;
        if ($ploting && $operatorShiftSuffix) {
        $persiapanCompleted = $ploting->persiapanChecks->filter(function ($check) use ($operatorShiftSuffix) {
        return \Illuminate\Support\Str::endsWith($check->item_name, $operatorShiftSuffix) && $check->operator_checked;
        })->isNotEmpty();
        }

        $shiftExpired = false;
        if ($ploting && $ploting->status === 'on_progress') {
        $jadwal = \App\Models\JadwalKerja::where('user_id', $user->id)
        ->whereDate('tanggal', \Carbon\Carbon::today())
        ->where('status', 'aktif')
        ->first();

        if ($jadwal && $jadwal->jam_keluar) {
        try {
        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
        } catch (\Exception $e) {
        $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
        $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
        }

        $tanggalHariIni = \Carbon\Carbon::today();
        $jamMasukHariIni = $tanggalHariIni->copy()->setTimeFromTimeString($jamMasuk->format('H:i:s'));
        $jamKeluarHariIni = $tanggalHariIni->copy()->setTimeFromTimeString($jamKeluar->format('H:i:s'));

        if ($jamKeluarHariIni->lessThan($jamMasukHariIni)) {
        $jamKeluarHariIni->addDay();
        }

        if (now()->greaterThan($jamKeluarHariIni)) {
        $shiftExpired = true;
        }
        }
        }

        $targetShift = 0;
        if ($operatorShift === 'pagi') $targetShift = $planning->target_shift_pagi;
        elseif ($operatorShift === 'siang') $targetShift = $planning->target_shift_siang;
        elseif ($operatorShift === 'malam') $targetShift = $planning->target_shift_malam;

        // Use shift target if defined, otherwise fallback to total day target
        $displayTarget = $targetShift > 0 ? $targetShift : $planning->qty_planning_produksi;
        
        // Count production specifically for this shift
        $shiftTotal = \App\Models\ProduksiPerJam::where('planning_id', $planning->id)
            ->where('shift', $operatorShift)
            ->sum(\DB::raw('ok_count + ng_part'));

        $gap = max(0, $displayTarget - $shiftTotal);
        
        $percentage = $displayTarget > 0
        ? min(100, round(($shiftTotal / $displayTarget) * 100, 1))
        : 0;
        
        $total = $ok + $ng; // This is day total for efficiency calculation
        $efficiency = $total > 0 ? round(($ok / $total) * 100, 1) : 100;
        $isCompleted = $gap <= 0;
        $progressBarClass=$percentage>= 100 ? 'bg-success' : ($percentage >= 70 ? 'bg-info' : 'bg-warning');

        // Check for active ANDON call (not yet resolved)
        $activeCall = \App\Models\DowntimeEvent::where('mesin_id', $planning->mesin_id)
            ->whereIn('status', ['pending', 'escalated'])
            ->where('title', 'like', 'CALL:%')
            ->first();
        @endphp

            {{-- ================= PERSIAPAN PRODUKSI MODAL ================= --}}
            <div class="modal fade" id="persiapanModal-{{ $planning->id }}" tabindex="-1" role="dialog" aria-labelledby="persiapanModalLabel-{{ $planning->id }}" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-bottom-0">
                            <h5 class="modal-title" id="persiapanModalLabel-{{ $planning->id }}">
                                <i class="fas fa-clipboard-check mr-2"></i>Persiapan Produksi
                                <span class="badge badge-warning ml-2">Shift: {{ ucfirst($operatorShift ?? 'Pagi') }}</span>
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="closeModal-{{ $planning->id }}" style="display: none;" disabled>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover table-dark" id="persiapanTable-{{ $planning->id }}">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%">Item Pengecekan</th>
                                            <th class="text-center {{ $operatorShift == 'pagi' ? 'active-shift-highlight' : '' }}" style="width: 10%">Pagi</th>
                                            <th class="text-center {{ $operatorShift == 'siang' ? 'active-shift-highlight' : '' }}" style="width: 10%">Siang</th>
                                            <th class="text-center {{ $operatorShift == 'malam' ? 'active-shift-highlight' : '' }}" style="width: 10%">Malam</th>
                                            <th style="width: 35%">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $items = [
                                        'Briefing dan tukar informasi',
                                        'Kesiapan Produk WIP & Subpart',
                                        'Kesiapan Packing',
                                        'Meja proses tersedia, lampu penerangan menyala',
                                        'Kelengkapan dokumen IK, Q-Point, dan Std Packing',
                                        'Pengecekan 5R',
                                        'Pemeriksaan kondisi subpart (sesuai dengan planning, CKD, CBU, Reguler)',
                                        'Kesiapan Tag Label, Marker, dan Cutter',
                                        'Ketersediaan limit sampel produk'
                                        ];
                                        @endphp
                                        @foreach ($items as $index => $item)
                                        <tr data-item="{{ $item }}">
                                            <td>{{ $index + 1 }}. {{ $item }}</td>
                                            <td class="text-center {{ $operatorShift == 'pagi' ? 'active-shift-highlight' : '' }}">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input check-pagi"
                                                        id="check_pagi_{{ $planning->id }}_{{ $index }}"
                                                        {{ $operatorShift != 'pagi' ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="check_pagi_{{ $planning->id }}_{{ $index }}"></label>
                                                </div>
                                            </td>
                                            <td class="text-center {{ $operatorShift == 'siang' ? 'active-shift-highlight' : '' }}">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input check-siang"
                                                        id="check_siang_{{ $planning->id }}_{{ $index }}"
                                                        {{ $operatorShift != 'siang' ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="check_siang_{{ $planning->id }}_{{ $index }}"></label>
                                                </div>
                                            </td>
                                            <td class="text-center {{ $operatorShift == 'malam' ? 'active-shift-highlight' : '' }}">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input check-malam"
                                                        id="check_malam_{{ $planning->id }}_{{ $index }}"
                                                        {{ $operatorShift != 'malam' ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="check_malam_{{ $planning->id }}_{{ $index }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm input-keterangan bg-dark text-white border-secondary"
                                                    placeholder="Keterangan"
                                                    data-item-index="{{ $index }}"
                                                    data-planning-id="{{ $planning->id }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-primary btn-save-persiapan"
                                data-ploting-id="{{ $ploting->id }}"
                                data-planning-id="{{ $planning->id }}"
                                data-operator-shift="{{ $operatorShift }}">
                                <i class="fas fa-save mr-1"></i> Simpan Persiapan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= NG PART SELECTION MODAL ================= --}}
            <div class="modal fade" id="ngPartModal-{{ $planning->id }}" tabindex="-1" role="dialog" aria-labelledby="ngPartModalLabel-{{ $planning->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="ngPartModalLabel-{{ $planning->id }}">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Pilih Jenis NG Part
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="selectedCategory-{{ $planning->id }}" value="">
                            <p class="text-muted mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pilih jenis NG Part yang ditemukan:
                            </p>
                            <div class="row">
                                @php
                                // Fallback list jika ngPartTypes tidak tersedia
                                $defaultNgParts = [
                                ['id' => 1, 'name' => 'Short Mold', 'code' => 'SM'],
                                ['id' => 2, 'name' => 'Silver', 'code' => 'SV'],
                                ['id' => 3, 'name' => 'Weldline', 'code' => 'WL'],
                                ['id' => 4, 'name' => 'Hairline', 'code' => 'HL'],
                                ['id' => 5, 'name' => 'Burry/Flash', 'code' => 'BF'],
                                ['id' => 6, 'name' => 'Crack', 'code' => 'CR'],
                                ['id' => 7, 'name' => 'Dirty', 'code' => 'DT'],
                                ['id' => 8, 'name' => 'Overcut', 'code' => 'OC'],
                                ['id' => 9, 'name' => 'Yellow wish', 'code' => 'YW'],
                                ['id' => 10, 'name' => 'Shining', 'code' => 'SH'],
                                ['id' => 11, 'name' => 'Scrath', 'code' => 'SC'],
                                ['id' => 12, 'name' => 'Blackdot', 'code' => 'BD'],
                                ['id' => 13, 'name' => 'Flowmark', 'code' => 'FM'],
                                ['id' => 14, 'name' => 'Gasmark/Airtrap', 'code' => 'GA'],
                                ['id' => 15, 'name' => 'Sinkmark', 'code' => 'SK'],
                                ['id' => 16, 'name' => 'Bending', 'code' => 'BG'],
                                ['id' => 17, 'name' => 'Bubble', 'code' => 'BB'],
                                ['id' => 18, 'name' => 'Kontaminasi', 'code' => 'KN'],
                                ['id' => 19, 'name' => 'Ejector mark', 'code' => 'EM'],
                                ['id' => 20, 'name' => 'Whiteness', 'code' => 'WH'],
                                ['id' => 21, 'name' => 'Discolour', 'code' => 'DC'],
                                ];

                                // Gunakan ngPartTypes jika ada, jika tidak gunakan default
                                $ngPartsToShow = isset($ngPartTypes) && $ngPartTypes->count() > 0
                                ? $ngPartTypes
                                : collect($defaultNgParts)->map(function($item) {
                                return (object)$item;
                                });
                                @endphp

                                @foreach($ngPartsToShow as $ngPart)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card ng-part-card border" style="cursor: pointer; transition: all 0.2s;"
                                        data-ng-part-id="{{ $ngPart->id ?? $ngPart['id'] }}"
                                        data-planning-id="{{ $planning->id }}">
                                        <div class="card-body text-center p-2">
                                            <h6 class="mb-0">{{ $ngPart->name ?? $ngPart['name'] }}</h6>
                                            @if(isset($ngPart->code) && $ngPart->code)
                                            <small class="text-muted">({{ $ngPart->code }})</small>
                                            @elseif(isset($ngPart['code']) && $ngPart['code'])
                                            <small class="text-muted">({{ $ngPart['code'] }})</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PRODUCTION CARD ================= --}}
            <div class="card production-card production-card-custom mb-5 border-0 shadow-lg overflow-hidden"
                data-planning-id="{{ $planning->id }}">

                <div class="card-header position-relative p-4 border-0 {{ $planning->bagian === 'assy' ? 'header-bg-assy' : 'header-bg-inject' }}" style="overflow: hidden;">

                    {{-- Decorative pattern overlay --}}
                    <div class="decorative-pattern"></div>

                    <div class="d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper mr-3 d-flex align-items-center justify-content-center">
                                <i class="fas {{ $planning->bagian === 'assy' ? 'fa-tools' : 'fa-cog' }} text-white fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 text-white font-weight-bold card-title-custom" style="letter-spacing: 1px; font-size: 1.5rem;">
                                    {{ $planning->bagian === 'assy' ? 'ASSEMBLY LINE' : 'INJECTION AREA' }}
                                </h3>
                                <div class="text-white-50 d-flex align-items-center mt-1" style="font-size: 0.85rem;">
                                    <i class="far fa-clock mr-2"></i>
                                    <span class="font-weight-medium">Session: {{ $startTime->format('H:i') }} — {{ $endTime->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="target-badge d-inline-flex align-items-center px-4 py-2">
                                <div class="mr-3">
                                    <small class="d-block text-uppercase text-muted font-weight-bold" style="font-size: 0.6rem; letter-spacing: 1px; line-height: 1;">Target (Shift)</small>
                                    <span class="h4 mb-0 font-weight-bold text-primary">{{ number_format($displayTarget) }}</span>
                                </div>
                                <div class="target-icon text-primary">
                                    <i class="fas fa-bullseye fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-stretch">
                        {{-- LEFT SIDE: PART & MACHINE INFO --}}
                        <div class="col-lg-3 col-md-12 mb-4 mb-lg-0 order-2 order-lg-1">
                            <div class="card h-100 border-0 shadow-sm info-card-container">
                                <div class="card-header bg-transparent border-0 py-3 text-center">
                                    <h6 class="mb-0 text-primary font-weight-bold text-uppercase" style="letter-spacing: 1.5px; font-size: 0.75rem;">
                                        <i class="fas fa-info-circle mr-2"></i>Unit specifications
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="info-block mb-4">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Part Name</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white text-truncate mt-1" style="font-size: 1.1rem;">{{ $planning->part?->nama_part ?? 'N/A' }}</div>
                                    </div>
                                    <div class="info-block mb-4">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Part Code</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white mt-1" style="font-size: 1.1rem;">{{ $planning->part?->no_parts ?? 'N/A' }}</div>
                                    </div>
                                    @if($planning->bagian === 'inject')
                                    <div class="info-block mb-4">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Machine</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white mt-1" style="font-size: 1.1rem;">{{ $planning->mesin?->nama_mesin ?? 'N/A' }}</div>
                                    </div>
                                    <div class="info-block mb-4">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Mold</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white mt-1" style="font-size: 1.1rem;">{{ $planning->moldshop?->nama_mold ?? 'N/A' }}</div>
                                    </div>
                                    <div class="info-block mb-0">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Customer</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white text-truncate mt-1" style="font-size: 1.1rem;">{{ $planning->moldshop?->customer ?? 'N/A' }}</div>
                                    </div>
                                    @else
                                    <div class="info-block mb-0">
                                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Meja</small>
                                        <div class="font-weight-bold text-dark dark-mode-text-white mt-1" style="font-size: 1.1rem;">{{ $planning->meja?->nama_meja ?? 'N/A' }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- CENTER: MAIN PRODUCTION BUTTONS --}}
                        <div class="col-lg-6 col-md-12 mb-4 mb-lg-0 order-1 order-lg-2">
                            <div class="action-section-container h-100 d-flex flex-column align-items-center justify-content-center px-lg-4" style="min-height: 520px;">
                                <h5 class="mb-4 text-center font-weight-bold pb-2 control-panel-title">
                                    <i class="fas fa-edit text-primary mr-2"></i>CONTROL PANEL
                                </h5>

                                @if ($isCompleted)
                                <div class="alert alert-success text-center w-100 py-5 shadow-lg" style="border-radius: 25px;">
                                    <i class="fas fa-check-circle fa-4x mb-3 text-success"></i>
                                    <h3 class="font-weight-bold">Status: COMPLETE!</h3>
                                </div>
                                @elseif ($shiftExpired)
                                <div class="alert alert-warning text-center w-100 py-5 shadow-lg" style="border-radius: 25px;">
                                    <i class="fas fa-clock fa-4x mb-3 text-warning"></i>
                                    <h3 class="font-weight-bold">Status: SHIFT ENDED</h3>
                                </div>
                                @elseif (!$persiapanCompleted)
                                <div class="alert alert-info text-center w-100 py-5 shadow-lg" style="border-radius: 25px;">
                                    <i class="fas fa-clipboard-check fa-4x mb-3 text-info"></i>
                                    <h3 class="font-weight-bold">BELUM PERSIAPAN</h3>
                                </div>
                                @elseif ($activeCall)
                                <div class="alert alert-danger text-center w-100 py-4 shadow-lg mb-4" style="border-radius: 25px;">
                                    <div class="pulsate-icon mb-3">
                                        <i class="fas fa-bullhorn fa-4x text-white"></i>
                                    </div>
                                    <h3 class="font-weight-bold text-white mb-2">WAITING FOR SUPPORT</h3>
                                    <p class="text-white-50 mb-0">Help requested for: <strong>{{ str_replace('CALL: ', '', $activeCall->title) }}</strong></p>
                                    <p class="text-white-50 small mt-2">Downtime is active. Production buttons disabled until resolved by Leader.</p>
                                </div>

                                {{-- Still show CALL button in case they need to re-call or change category, or just to keep layout consistent --}}
                                <button type="button" class="btn btn-warning btn-lg shadow-lg w-100 btn-andon-trigger btn-action-custom btn-call-custom"
                                    data-planning-id="{{ $planning->id }}"
                                    data-part-name="{{ $planning->part->nama_part ?? 'Part' }}">
                                    <i class="fas fa-bullhorn fa-3x mr-4"></i>
                                    <strong style="font-size: 2.2rem; letter-spacing: 2px;">RE-CALL</strong>
                                </button>
                                @else
                                <div class="w-100 d-flex flex-column align-items-center p-2">
                                    {{-- GOOD BUTTON --}}
                                    <button type="button" class="btn btn-success btn-lg btn-action btn-ok btn-action-custom btn-ok-custom shadow-lg mb-4 w-100"
                                        data-planning="{{ $planning->id }}" data-planning-id="{{ $planning->id }}">
                                        <i class="fas fa-check-circle fa-3x mr-4"></i>
                                        <strong style="font-size: 2.2rem; letter-spacing: 2px;">GOOD</strong>
                                    </button>

                                    {{-- DEFECT BUTTON --}}
                                    <button type="button" class="btn btn-danger btn-lg btn-action btn-defect-direct btn-action-custom btn-defect-custom shadow-lg mb-4 w-100"
                                        data-planning-id="{{ $planning->id }}">
                                        <i class="fas fa-times-circle fa-3x mr-4"></i>
                                        <strong style="font-size: 2.2rem; letter-spacing: 2px;">DEFECT</strong>
                                    </button>

                                    {{-- CALL BUTTON --}}
                                    <button type="button" class="btn btn-warning btn-lg shadow-lg w-100 btn-andon-trigger btn-action-custom btn-call-custom"
                                        data-planning-id="{{ $planning->id }}"
                                        data-part-name="{{ $planning->part->nama_part ?? 'Part' }}">
                                        <i class="fas fa-bullhorn fa-3x mr-4"></i>
                                        <strong style="font-size: 2.2rem; letter-spacing: 2px;">CALL</strong>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- RIGHT SIDE: PRODUCTION STATISTICS --}}
                        <div class="col-lg-3 col-md-12 order-3 order-lg-3">
                            <div class="card h-100 border-0 shadow-sm info-card-container">
                                <div class="card-header bg-transparent border-0 py-3 text-center">
                                    <h6 class="mb-0 text-primary font-weight-bold text-uppercase" style="letter-spacing: 1.5px; font-size: 0.75rem;">
                                        <i class="fas fa-chart-line mr-2"></i>Performance Stat
                                    </h6>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div class="stat-box p-3 mb-3 stat-box-container stat-box-blue">
                                        <div class="mr-3 text-primary"><i class="fas fa-bullseye fa-lg"></i></div>
                                        <div>
                                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 0.6rem; letter-spacing: 1px;">Target (Shift)</small>
                                            <div class="h4 mb-0 font-weight-bold text-dark planned-qty">{{ number_format($displayTarget) }}</div>
                                        </div>
                                    </div>
                                    <div class="stat-box p-3 mb-3 stat-box-container stat-box-green">
                                        <div class="mr-3 text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                                        <div>
                                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 0.6rem; letter-spacing: 1px;">Good</small>
                                            <div class="h4 mb-0 font-weight-bold text-dark ok-count">{{ number_format($ok) }}</div>
                                        </div>
                                    </div>
                                    <div class="stat-box p-3 mb-3 stat-box-container stat-box-red">
                                        <div class="mr-3 text-danger"><i class="fas fa-times-circle fa-lg"></i></div>
                                        <div>
                                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 0.6rem; letter-spacing: 1px;">Defect</small>
                                            <div class="h4 mb-0 font-weight-bold text-dark ng-count">{{ number_format($ng) }}</div>
                                        </div>
                                    </div>
                                    <div class="stat-box p-3 stat-box-container stat-box-yellow">
                                        <div class="mr-3 text-warning"><i class="fas fa-hourglass-half fa-lg"></i></div>
                                        <div>
                                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 0.6rem; letter-spacing: 1px;">Gap</small>
                                            <div class="h4 mb-0 font-weight-bold text-dark gap-count">{{ number_format($gap) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    {{-- BOTTOM PROGRESS BAR --}}
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small text-uppercase font-weight-bold">Shift Progress</span>
                            <span class="text-primary h5 mb-0 font-weight-bold progress-info">{{ $percentage }}%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 10px; background: #333; border: 1px solid #444;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $progressBarClass }}"
                                role="progressbar"
                                @style(['width'=> $percentage . '%'])
                                aria-valuenow="{{ $percentage }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Total: <strong class="text-white total-count">{{ number_format($total) }}</strong> | Efficiency: <strong class="text-white efficiency-count">{{ $efficiency }}%</strong></small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            {{-- GLOBAL CALL FOR SUPPORT MODAL --}}
            <div class="modal fade" id="andonCallModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 9999;">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content modal-andon-content">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h4 class="modal-title w-100 text-center text-white mt-3 font-weight-bold">
                                <i class="fas fa-bullhorn mr-2 text-primary"></i>CALL FOR SUPPORT
                            </h4>
                            <button type="button" class="close text-white" data-dismiss="modal" style="position: absolute; right: 20px; top: 20px;">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <span class="badge badge-primary px-3 py-2" id="andonPartBadge" style="font-size: 1rem; border-radius: 10px;">Select Help Category</span>
                            </div>
                            <input type="hidden" id="andonPlanningId">
                            <div class="grid-categories" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <button class="btn btn-dark border-secondary p-4 btn-andon-select text-white d-flex flex-column align-items-center btn-andon-category" data-category="material">
                                    <i class="fas fa-cube fa-3x mb-2 text-danger"></i>
                                    <span style="font-size: 1.3rem;">Material</span>
                                </button>
                                <button class="btn btn-dark border-secondary p-4 btn-andon-select text-white d-flex flex-column align-items-center btn-andon-category" data-category="machine">
                                    <i class="fas fa-hdd fa-3x mb-2 text-danger"></i>
                                    <span style="font-size: 1.3rem;">Machine</span>
                                </button>
                                <button class="btn btn-dark border-secondary p-4 btn-andon-select text-white d-flex flex-column align-items-center btn-andon-category" data-category="process">
                                    <i class="fas fa-cogs fa-3x mb-2 text-danger"></i>
                                    <span style="font-size: 1.3rem;">Process</span>
                                </button>
                                <button class="btn btn-dark border-secondary p-4 btn-andon-select text-white d-flex flex-column align-items-center btn-andon-category" data-category="operator">
                                    <i class="fas fa-user fa-3x mb-2 text-danger"></i>
                                    <span style="font-size: 1.3rem;">Operator</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/operator-production.css') }}">
            @endpush

            @push('scripts')
            <!-- External Production Logic -->
            <script src="{{ asset('js/operator-production.js') }}"></script>
            <!-- Persiapan Produksi JS -->
            <script src="{{ asset('js/persiapan-produksi.js') }}"></script>

            @if (!empty($planningsToOpen))
            <script>
                $(document).ready(function() {
                    // Delay slightly to ensure modals are initialized
                    setTimeout(function() {
                        if (typeof window.openPersiapanModals === 'function') {
                            window.openPersiapanModals(<?php echo json_encode($planningsToOpen); ?>);
                        } else {
                            console.error('openPersiapanModals function not found');
                        }
                    }, 500);
                });
            </script>
            @endif
            @endpush