@extends('layouts.app')

@php
$user = Auth::user();
$userRole = $user ? strtolower($user->role) : '';
$isAdmin = $user && in_array($userRole, ['super_admin', 'kabag', 'manager_plant', 'leader']);

// Izin Spesifik
$canEditPlan = $isAdmin || ($user && $userRole === 'staff');
$canEditActual = $isAdmin || ($user && in_array($userRole, ['operator', 'staff']));
@endphp

@push('css')
    <link href="{{ asset('css/jadwal-pemeliharaan.css') }}?v={{ time() }}" rel="stylesheet">
    <style>
        /* Perbaikan Instan untuk Mode Gelap agar KOLOM KIRI TIDAK HILANG */
        .solid-bg { background-color: #ffffff !important; color: #000000 !important; }
        .dark-mode .solid-bg, body[data-theme="dark"] .solid-bg { background-color: #343a40 !important; color: #ffffff !important; }
        
        .target-plan-bg { background-color: #f8f9fa !important; color: #007bff !important; }
        .dark-mode .target-plan-bg, body[data-theme="dark"] .target-plan-bg { background-color: #2b3035 !important; color: #6ea8fe !important; }

        .target-actual-bg { background-color: #e2e3e5 !important; color: #198754 !important; }
        .dark-mode .target-actual-bg, body[data-theme="dark"] .target-actual-bg { background-color: #212529 !important; color: #75b798 !important; }
        
        /* Pastikan thead lebih tinggi dari tbody sticky */
        .table-pm thead th { z-index: 40 !important; }
        .table-pm tbody td.sticky-col { z-index: 25 !important; }
    </style>
@endpush

@section('content')
<!-- Hidden iframe for inline printing safely off-screen -->
<iframe id="printFrame" style="position: absolute; top: -9999px; left: -9999px; width: 0; height: 0; border: none; opacity: 0;" src=""></iframe>

<div class="container-fluid pb-5">
    
    <!-- UI WEB: KONTROL FILTER DAN CETAK -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4 d-print-none">
        <h1 class="h3 mb-0 text-white-shadow"><i class="fas fa-calendar-check text-primary"></i> Jadwal Pemeliharaan</h1>
        
        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('jadwal-pemeliharaan.index') }}" class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <label class="mb-0 fw-bold me-2 text-white">TAHUN :</label>
                    <select name="tahun" class="form-control border-primary fw-bold shadow-sm" onchange="this.form.submit()" style="width: 130px;">
                        @for ($i = date('Y') - 1; $i <= date('Y') + 3; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <button type="button" 
                    data-url="{{ route('jadwal-pemeliharaan.print', ['tahun' => $tahun]) }}"
                    onclick="printReport(this.getAttribute('data-url'))" 
                    class="btn btn-danger fw-bold shadow-sm ms-3">
                    <i class="fas fa-print me-2"></i> Cetak Laporan
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4 bg-dark-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-pm">
                <table class="table table-pm table-bordered border-dark m-0">
                    <thead>
                        <tr class="text-center">
                            <!-- Lebar dan Left offset yang presisi agar sticky tidak bocor -->
                            <!-- Pengaturan Lebar Kolom Dipertegas, Padding disesuaikan (px-2) -->
                            <th rowspan="2" class="sticky-col align-middle bg-primary text-white px-2" style="width: 45px; min-width: 45px; max-width: 45px; left: 0;">No</th>
                            <th rowspan="2" class="sticky-col align-middle bg-primary text-white px-2" style="width: 180px; min-width: 180px; max-width: 180px; left: 45px;">Nama Mesin</th>
                            <th rowspan="2" class="sticky-col align-middle bg-primary text-white px-2" style="width: 80px; min-width: 80px; max-width: 80px; left: 225px; white-space: nowrap;">NOMOR</th>
                            <th rowspan="2" class="sticky-col align-middle px-1 fw-bold bg-secondary text-white" style="font-size: 0.65rem; width: 60px; min-width: 60px; max-width: 60px; left: 305px;">Target</th>
                            
                            @for ($m = 1; $m <= 12; $m++)
                            <th colspan="4" class="text-uppercase text-white" style="background-color: #2c3e50 !important; font-size: 0.7rem; border: 1px solid #000;">
                                {{ Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                            </th>
                            @endfor
                            <th rowspan="2" class="align-middle px-3 text-white bg-primary" style="font-size: 0.7rem; min-width: 100px;">Ket</th>
                        </tr>
                        <tr class="text-center">
                            @for ($m = 1; $m <= 12; $m++)
                                @for ($w = 1; $w <= 4; $w++)
                                <th class="text-white" style="min-width: 25px; font-size: 0.55rem; background-color: #34495e; border: 1px solid #000;">
                                    @php $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']; @endphp
                                    {{ $romans[$w] }}
                                </th>
                                @endfor
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mesins as $index => $mesin)
                        @php
                            $mId = $mesin->id;
                            $mNama = $mesin->nama_mesin;
                            $mNo = substr($mNama, -2);
                        @endphp
                        <!-- Baris PLAN -->
                        <tr class="row-plan">
                            <td rowspan="2" class="sticky-col align-middle text-center border-dark fw-bold solid-bg px-2" style="left: 0;">
                                {{ $index + 1 }}
                            </td>
                            <td rowspan="2" class="sticky-col align-middle border-dark fw-bold solid-bg px-2" style="left: 45px; white-space: normal; word-break: break-word;">
                                {{ $mNama }}
                            </td>
                            <td rowspan="2" class="sticky-col align-middle text-center border-dark fw-bold solid-bg px-2" style="left: 225px;">
                                {{ $mNo }}
                            </td>
                            <td class="sticky-col align-middle fw-bold py-1 text-center border-dark target-plan-bg px-1" style="font-size: 0.55rem; left: 305px;">PLAN</td>
                            @for ($m = 1; $m <= 12; $m++)
                                @for ($w = 1; $w <= 4; $w++)
                                @php
                                    $jadwal = $jadwalMap[$mId][$m][$w] ?? null;
                                    $p = $jadwal ? $jadwal->plan_tipe : '';
                                    $a = $jadwal ? $jadwal->aktual_tanggal : '';
                                    $k = $jadwal ? $jadwal->keterangan : '';
                                @endphp
                                <td class="p-0 border-dark edit-trigger"
                                    data-mesin-id="{{ $mId }}"
                                    data-mesin-nama="{{ $mNama }}"
                                    data-bulan="{{ $m }}"
                                    data-minggu="{{ $w }}"
                                    data-type="plan"
                                    data-plan="{{ $p }}"
                                    data-actual="{{ $a }}"
                                    data-keterangan="{{ $k }}">
                                    @if(!empty($p))
                                        <div style="background-color: #006400 !important; color: #fff !important; width: 90%; height: 90%; border: 1px solid #000;" 
                                             class="d-flex align-items-center justify-content-center mx-auto mt-1 fw-bold" style="font-size: 0.5rem;">
                                            {{ $p }}
                                        </div>
                                    @endif
                                </td>
                                @endfor
                            @endfor
                            <td rowspan="2" class="align-middle border-dark px-2 text-white" style="font-size: 0.65rem;">
                                @php
                                    $latestKet = '';
                                    if(isset($jadwalMap[$mId])) {
                                        foreach($jadwalMap[$mId] as $mon) {
                                            foreach($mon as $j) { if(!empty($j->keterangan)) $latestKet = $j->keterangan; }
                                        }
                                    }
                                @endphp
                                {{ $latestKet }}
                            </td>
                        </tr>
                        <!-- Baris ACTUAL -->
                        <tr class="row-actual">
                            <td class="sticky-col align-middle fw-bold py-1 text-center border-dark target-actual-bg px-1" style="font-size: 0.55rem; left: 305px;">ACTUAL</td>
                            @for ($m = 1; $m <= 12; $m++)
                                @for ($w = 1; $w <= 4; $w++)
                                @php
                                    $jadwal = $jadwalMap[$mId][$m][$w] ?? null;
                                    $p = $jadwal ? $jadwal->plan_tipe : '';
                                    $a = $jadwal ? $jadwal->aktual_tanggal : '';
                                    $k = $jadwal ? $jadwal->keterangan : '';
                                @endphp
                                <td class="p-0 border-dark text-center edit-trigger"
                                    data-mesin-id="{{ $mId }}"
                                    data-mesin-nama="{{ $mNama }}"
                                    data-bulan="{{ $m }}"
                                    data-minggu="{{ $w }}"
                                    data-type="actual"
                                    data-plan="{{ $p }}"
                                    data-actual="{{ $a }}"
                                    data-keterangan="{{ $k }}">
                                    <span class="fw-bold" style="font-size: 0.5rem;">{{ $a }}</span>
                                </td>
                                @endfor
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('jadwal-pemeliharaan.modal')

@endsection

@push('scripts')
<script>
    window.urlUpdateJadwal = "{{ route('jadwal-pemeliharaan.update-cell') }}";
    
    // Fungsi untuk mencetak file PDF di latar belakang secara aman
    function printReport(url) {
        var printFrame = document.getElementById('printFrame');
        
        // Hubungkan event onload untuk memicu print setelah file selesai dimuat di latar belakang
        printFrame.onload = function() {
            setTimeout(function() {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (e) {
                    console.error('Gagal mencetak dari iframe:', e);
                }
            }, 300); // jeda responsif
        };
        
        // Memuat URL ke dalam iframe
        printFrame.src = url;
    }
</script>
<script src="{{ asset('js/jadwal-pemeliharaan.js') }}?v={{ time() }}"></script>
@endpush