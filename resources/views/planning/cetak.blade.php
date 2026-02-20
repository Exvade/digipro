<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning Harian Produksi</title>
    <link rel="stylesheet" href="{{ asset('css/planning-cetak.css') }}">
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak
    </button>

    {{-- SECTION 1: PLANNING INJECT --}}
    @if($planningsInject->count() > 0)
    <div class="container" style="page-break-after: always;">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <strong>PT.MADA WIKRI TUNGGAL</strong>
            </div>

            <div class="header-center">
                <h2>PLANNING HARIAN PRODUKSI - INJECT</h2>
            </div>

            <div class="header-right">
                <table class="approval-table">
                    <tr>
                        <td class="label">Dikonfirmasi</td>
                        <td class="label">Diperiksa</td>
                        <td class="label">Dibuat</td>
                    </tr>
                    <tr>
                        <td class="signature-space">
                            {{-- QR Code untuk Konfirmasi (kosong untuk manual) --}}
                        </td>
                        <td class="signature-space">
                            @php
                            // Cari planning INJECT yang sudah di-approve dan memiliki approval_qr_code (Ariyanto)
                            $planningApproved = null;
                            foreach ($planningsInject as $planning) {
                            if (isset($planning->status_approval)
                            && $planning->status_approval === 'approved'
                            && isset($planning->approval_qr_code)
                            && !empty(trim($planning->approval_qr_code))
                            && $planning->approval_qr_code !== null) {
                            $planningApproved = $planning;
                            break;
                            }
                            }
                            @endphp
                            @if($planningApproved && !empty(trim($planningApproved->approval_qr_code)))
                            <img src="{{ route('planning.qrcode.secure', ['id' => $planningApproved->id, 'type' => 'approval']) }}"
                                alt="QR Code Approval Ariyanto"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;"
                                onerror="console.error('QR Code Approval gagal dimuat untuk planning ID: {{ $planningApproved->id }}'); this.style.display='none';">
                            @endif
                        </td>
                        <td class="signature-space">
                            @php
                            // Cari planning INJECT yang memiliki signature_qr_code (Diar)
                            $planningWithSignature = $planningsInject->filter(function($planning) {
                            return !empty($planning->signature_qr_code);
                            })->first();
                            @endphp
                            @if($planningWithSignature && !empty($planningWithSignature->signature_qr_code))
                            <img src="{{ route('planning.qrcode.secure', ['id' => $planningWithSignature->id, 'type' => 'signature']) }}"
                                alt="QR Code Tanda Tangan Pak Diar"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;">
                            @else
                            <img src="{{ route('secure.signature') }}"
                                alt="Tanda Tangan Pak Diar"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;">
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{-- Tanda tangan konfirmasi akan diisi manual --}}
                        </td>
                        <td>Ariyanto</td>
                        <td>{{ $creatorName ?? 'Diar W' }}</td>
                    </tr>
                    <tr>
                        <td class="date-cell">
                            {{-- Tanggal konfirmasi --}}
                        </td>
                        <td class="date-cell">
                            @if($planningApproved && $planningApproved->approved_at)
                            {{ \Carbon\Carbon::parse($planningApproved->approved_at)->format('d-m-Y') }}
                            @endif
                        </td>
                        <td class="date-cell">
                            {{ $creatorDate ?? now()->format('d-m-Y') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- 🔸 Revisi dipindahkan ke luar header-right -->
        <div class="revision">Revisi: 00</div>

        <!-- Main Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no-mold">NO<br>MOLD</th>
                    <th rowspan="2" class="col-no">NO</th>
                    <th rowspan="2" class="col-tanggal">TANGGAL</th>
                    <th rowspan="2" class="col-mesin">MESIN</th>
                    <th rowspan="2" class="col-lot">LOT PRODUKSI</th>
                    <th rowspan="2" class="col-kode">KODE PART</th>
                    <th rowspan="2" class="col-nomor">NOMOR BARANG</th>
                    <th rowspan="2" class="col-nama">NAMA BARANG</th>
                    <th rowspan="2" class="col-qty">Qty PLANNING<br>PRODUKSI</th>

                    {{-- 🌟 Tambahan kolom kosong di sini --}}
                    <th rowspan="2" class="col-empty" style="width:70px; background-color:#fdfdfd;"></th>

                    <th colspan="8" class="shift-pagi">SHIFT I</th>
                    <th colspan="8" class="shift-siang">SHIFT II</th>
                    <th colspan="8" class="shift-malam">SHIFT III</th>
                    <th colspan="3">TARGET<br>SHIFT</th>
                    <th rowspan="2" class="col-qty">TOTAL<br>SHIFT</th>
                </tr>
                <tr>
                    @for ($i = 7; $i <= 14; $i++)
                        <th class="col-jam shift-pagi">{{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i + 1) }}
                        </th>
                        @endfor
                        @for ($i = 15; $i <= 22; $i++)
                            <th class="col-jam shift-siang">
                            {{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i + 1) }}
                            </th>
                            @endfor
                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <th class="col-jam shift-malam">
                                {{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i == 23 ? 0 : $i + 1) }}
                            </th>
                            @endforeach
                            <th class="col-target target-pagi">I</th>
                            <th class="col-target target-siang">II</th>
                            <th class="col-target target-malam">III</th>
                </tr>
            </thead>

            <tbody>
                @php
                $currentMold = null;
                $moldRowspan = [];

                foreach ($planningsInject as $planning) {
                $moldId = $planning->moldshop_id ?? 'no-mold';
                if (!isset($moldRowspan[$moldId])) {
                $moldRowspan[$moldId] = 0;
                }
                // Karena sekarang setiap planning punya 3 baris (Target, Pencapaian, Transaksi)
                $moldRowspan[$moldId] += 3;
                }
                @endphp

                @foreach ($planningsInject as $index => $planning)
                @php
                $moldId = $planning->moldshop_id ?? 'no-mold';
                $showMoldCell = $currentMold !== $moldId;
                $currentMold = $moldId;

                $statusClass = '';
                $statusText = $planning->status ?? '';
                if (
                stripos($statusText, 'progress') !== false ||
                stripos($statusText, 'installing') !== false
                ) {
                $statusClass = 'status-on-progress';
                $statusText = 'ON PROGRESS INSTALLING';
                } elseif (stripos($statusText, 'maintenance') !== false) {
                $statusClass = 'status-maintenance';
                $statusText = 'ON MAINTENANCE';
                }

                $totalShift =
                ($planning->target_shift_pagi ?? 0) +
                ($planning->target_shift_siang ?? 0) +
                ($planning->target_shift_malam ?? 0);
                @endphp

                {{-- ========== BARIS 1: TARGET ========== --}}
                <tr class="row-target">
                    @if ($showMoldCell)
                    <td rowspan="{{ $moldRowspan[$moldId] }}" class="col-no-mold">
                        {{ $planning->moldshop->no_mold ?? '-' }}
                    </td>
                    @endif

                    <td rowspan="3">{{ $index + 1 }}</td>
                    <td rowspan="3">{{ \Carbon\Carbon::parse($planning->tanggal)->format('d-m-Y') }}</td>
                    <td rowspan="3" class="{{ $statusClass }}">
                        {{ $planning->mesin->nama_mesin ?? '-' }}
                        @if ($statusClass)
                        <br><small style="font-size: 7px;">{{ $statusText }}</small>
                        @endif
                    </td>
                    <td rowspan="3">{{ $planning->lot_produksi ?? '-' }}</td>
                    <td rowspan="3">{{ $planning->part->no_parts ?? '-' }}</td>
                    <td rowspan="3">{{ $planning->part->no_barang ?? '-' }}</td>
                    <td rowspan="3" style="text-align: left; padding-left: 5px;">
                        {{ $planning->part->nama_part ?? '-' }}
                    </td>
                    <td rowspan="3" class="col-qty">{{ number_format($planning->qty_planning_produksi ?? 0) }}
                    </td>

                    {{-- 🌟 Kolom baru: TARGET --}}
                    <td class="col-empty">TARGET</td>

                    {{-- Data jam SHIFT I --}}
                    @for ($i = 7; $i <= 14; $i++)
                        @php
                        $hourData=$planning->jam->where('shift', 'pagi')->where('jam', sprintf('%02d:00:00', $i))->first();
                        $target = $hourData ? $hourData->target : '';
                        @endphp
                        <td class="shift-pagi">{{ $target }}</td>
                        @endfor

                        {{-- SHIFT II --}}
                        @for ($i = 15; $i <= 22; $i++)
                            @php
                            $hourData=$planning->jam->where('shift', 'siang')->where('jam', sprintf('%02d:00:00', $i))->first();
                            $target = $hourData ? $hourData->target : '';
                            @endphp
                            <td class="shift-siang">{{ $target }}</td>
                            @endfor

                            {{-- SHIFT III --}}
                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            @php
                            $hourData = $planning->jam->where('shift', 'malam')->where('jam', sprintf('%02d:00:00', $i))->first();
                            $target = $hourData ? $hourData->target : '';
                            @endphp
                            <td class="shift-malam">{{ $target }}</td>
                            @endforeach

                            {{-- Target dari database --}}
                            <td class="target-pagi">
                                {{ $planning->target_shift_pagi ? number_format($planning->target_shift_pagi) : '' }}
                            </td>
                            <td class="target-siang">
                                {{ $planning->target_shift_siang ? number_format($planning->target_shift_siang) : '' }}
                            </td>
                            <td class="target-malam">
                                {{ $planning->target_shift_malam ? number_format($planning->target_shift_malam) : '' }}
                            </td>
                            <td rowspan="3">{{ $totalShift > 0 ? number_format($totalShift) : '' }}</td>
                </tr>

                {{-- ========== BARIS 2: PENCAPAIAN ========== --}}
                <tr class="row-pencapaian">
                    <td class="col-empty">PENCAPAIAN</td>

                    @for ($i = 7; $i <= 14; $i++)
                        <td class="shift-pagi">
                        </td>
                        @endfor

                        @for ($i = 15; $i <= 22; $i++)
                            <td class="shift-siang">
                            </td>
                            @endfor

                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <td class="shift-malam"></td>
                            @endforeach

                            <td class="target-pagi">0%</td>
                            <td class="target-siang">0%</td>
                            <td class="target-malam">0%</td>
                </tr>

                {{-- ========== BARIS 3: TRANSAKSI ========== --}}
                <tr class="row-transaksi">
                    <td class="col-empty">TRANSAKSI</td>

                    @for ($i = 7; $i <= 14; $i++)
                        <td class="shift-pagi">
                        </td>
                        @endfor

                        @for ($i = 15; $i <= 22; $i++)
                            <td class="shift-siang">
                            </td>
                            @endfor

                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <td class="shift-malam"></td>
                            @endforeach

                            <td class="target-pagi"></td>
                            <td class="target-siang"></td>
                            <td class="target-malam"></td>
                </tr>

                {{-- Garis pembatas antar mold --}}
                @if (!$loop->last)
                @php
                $nextPlanning = $planningsInject[$index + 1] ?? null;
                $nextMoldId = $nextPlanning ? $nextPlanning->moldshop_id ?? 'no-mold' : null;
                @endphp
                @if ($moldId !== $nextMoldId)
                <tr class="row-pemisah">
                    <td colspan="38" style="height: 3px; background-color: #000;"></td>
                </tr>
                @endif
                @endif
                @endforeach
            </tbody>

        </table>
        <!-- Footer -->
        <div class="footer-text-right">FORM-PPIC-3-12 REV01</div>
    </div>
    @endif

    {{-- SECTION 2: PLANNING ASSEMBLY --}}
    @if($planningsAssy->count() > 0)
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <strong>PT.MADA WIKRI TUNGGAL</strong>
            </div>

            <div class="header-center">
                <h2>PLANNING HARIAN PRODUKSI - ASSEMBLY</h2>
            </div>

            <div class="header-right">
                <table class="approval-table">
                    <tr>
                        <td class="label">Dikonfirmasi</td>
                        <td class="label">Diperiksa</td>
                        <td class="label">Dibuat</td>
                    </tr>
                    <tr>
                        <td class="signature-space">
                            {{-- QR Code untuk Konfirmasi (kosong untuk manual) --}}
                        </td>
                        <td class="signature-space">
                            @php
                            // Cari planning ASSY yang sudah di-approve
                            $planningApprovedAssy = null;
                            foreach ($planningsAssy as $planning) {
                            if (isset($planning->status_approval)
                            && $planning->status_approval === 'approved'
                            && isset($planning->approval_qr_code)
                            && !empty(trim($planning->approval_qr_code))
                            && $planning->approval_qr_code !== null) {
                            $planningApprovedAssy = $planning;
                            break;
                            }
                            }
                            @endphp
                            @if($planningApprovedAssy && !empty(trim($planningApprovedAssy->approval_qr_code)))
                            <img src="{{ route('planning.qrcode.secure', ['id' => $planningApprovedAssy->id, 'type' => 'approval']) }}"
                                alt="QR Code Approval Ariyanto"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;"
                                onerror="console.error('QR Code Approval gagal dimuat'); this.style.display='none';">
                            @endif
                        </td>
                        <td class="signature-space">
                            @php
                            // Cari planning ASSY yang memiliki signature_qr_code (Diar)
                            $planningWithSignatureAssy = $planningsAssy->filter(function($planning) {
                            return !empty($planning->signature_qr_code);
                            })->first();
                            @endphp
                            @if($planningWithSignatureAssy && !empty($planningWithSignatureAssy->signature_qr_code))
                            <img src="{{ route('planning.qrcode.secure', ['id' => $planningWithSignatureAssy->id, 'type' => 'signature']) }}"
                                alt="QR Code Tanda Tangan Pak Diar"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;">
                            @else
                            <img src="{{ route('secure.signature') }}"
                                alt="Tanda Tangan Pak Diar"
                                style="width: 35px; height: 35px; display: block; margin: 0 auto;">
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{-- Tanda tangan konfirmasi akan diisi manual --}}
                        </td>
                        <td>Ariyanto</td>
                        <td>{{ $creatorName ?? 'Diar W' }}</td>
                    </tr>
                    <tr>
                        <td class="date-cell">
                            {{-- Tanggal konfirmasi --}}
                        </td>
                        <td class="date-cell">
                            @if($planningApprovedAssy && $planningApprovedAssy->approved_at)
                            {{ \Carbon\Carbon::parse($planningApprovedAssy->approved_at)->format('d-m-Y') }}
                            @endif
                        </td>
                        <td class="date-cell">
                            {{ $creatorDate ?? now()->format('d-m-Y') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- 🔸 Revisi dipindahkan ke luar header-right -->
        <div class="revision">Revisi: 00</div>

        <!-- Main Table untuk Assembly -->
        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">NO</th>
                    <th rowspan="2" class="col-tanggal">TANGGAL</th>
                    <th rowspan="2" class="col-mesin">MEJA</th>
                    <th rowspan="2" class="col-lot">LOT PRODUKSI</th>
                    <th rowspan="2" class="col-kode">KODE PART</th>
                    <th rowspan="2" class="col-nomor">NOMOR BARANG</th>
                    <th rowspan="2" class="col-nama">NAMA BARANG</th>
                    <th rowspan="2" class="col-qty">Qty PLANNING<br>PRODUKSI</th>

                    {{-- 🌟 Tambahan kolom kosong di sini --}}
                    <th rowspan="2" class="col-empty" style="width:70px; background-color:#fdfdfd;"></th>

                    <th colspan="8" class="shift-pagi">SHIFT I</th>
                    <th colspan="8" class="shift-siang">SHIFT II</th>
                    <th colspan="8" class="shift-malam">SHIFT III</th>
                    <th colspan="3">TARGET<br>SHIFT</th>
                    <th rowspan="2" class="col-qty">TOTAL<br>SHIFT</th>
                </tr>
                <tr>
                    @for ($i = 7; $i <= 14; $i++)
                        <th class="col-jam shift-pagi">{{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i + 1) }}
                        </th>
                        @endfor
                        @for ($i = 15; $i <= 22; $i++)
                            <th class="col-jam shift-siang">
                            {{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i + 1) }}
                            </th>
                            @endfor
                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <th class="col-jam shift-malam">
                                {{ sprintf('%02d', $i) }}<br>s/d<br>{{ sprintf('%02d', $i == 23 ? 0 : $i + 1) }}
                            </th>
                            @endforeach
                            <th class="col-target target-pagi">I</th>
                            <th class="col-target target-siang">II</th>
                            <th class="col-target target-malam">III</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($planningsAssy as $index => $planning)
                @php
                $statusClass = '';
                $statusText = $planning->status ?? '';
                if (
                stripos($statusText, 'progress') !== false ||
                stripos($statusText, 'installing') !== false
                ) {
                $statusClass = 'status-on-progress';
                $statusText = 'ON PROGRESS INSTALLING';
                } elseif (stripos($statusText, 'maintenance') !== false) {
                $statusClass = 'status-maintenance';
                $statusText = 'ON MAINTENANCE';
                }

                $totalShift =
                ($planning->target_shift_pagi ?? 0) +
                ($planning->target_shift_siang ?? 0) +
                ($planning->target_shift_malam ?? 0);
                @endphp

                {{-- ========== BARIS 1: TARGET ========== --}}
                <tr class="row-target">
                    <td rowspan="3">{{ $index + 1 }}</td>
                    <td rowspan="3">{{ \Carbon\Carbon::parse($planning->tanggal)->format('d-m-Y') }}</td>
                    <td rowspan="3" class="{{ $statusClass }}">
                        {{ $planning->meja->nama_meja ?? '-' }}
                        @if ($statusClass)
                        <br><small style="font-size: 7px;">{{ $statusText }}</small>
                        @endif
                    </td>
                    <td rowspan="3">{{ $planning->lot_produksi ?? '-' }}</td>
                    <td rowspan="3">{{ $planning->part->no_parts ?? '-' }}</td>
                    <td rowspan="3">{{ $planning->part->no_barang ?? '-' }}</td>
                    <td rowspan="3" style="text-align: left; padding-left: 5px;">
                        {{ $planning->part->nama_part ?? '-' }}
                    </td>
                    <td rowspan="3" class="col-qty">{{ number_format($planning->qty_planning_produksi ?? 0) }}
                    </td>

                    {{-- 🌟 Kolom baru: TARGET --}}
                    <td class="col-empty">TARGET</td>

                    {{-- Data jam SHIFT I --}}
                    @for ($i = 7; $i <= 14; $i++)
                        @php
                        $hourData=$planning->jam->where('shift', 'pagi')->where('jam', sprintf('%02d:00:00', $i))->first();
                        $target = $hourData ? $hourData->target : '';
                        @endphp
                        <td class="shift-pagi">{{ $target }}</td>
                        @endfor

                        {{-- SHIFT II --}}
                        @for ($i = 15; $i <= 22; $i++)
                            @php
                            $hourData=$planning->jam->where('shift', 'siang')->where('jam', sprintf('%02d:00:00', $i))->first();
                            $target = $hourData ? $hourData->target : '';
                            @endphp
                            <td class="shift-siang">{{ $target }}</td>
                            @endfor

                            {{-- SHIFT III --}}
                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            @php
                            $hourData = $planning->jam->where('shift', 'malam')->where('jam', sprintf('%02d:00:00', $i))->first();
                            $target = $hourData ? $hourData->target : '';
                            @endphp
                            <td class="shift-malam">{{ $target }}</td>
                            @endforeach

                            {{-- Target dari database --}}
                            <td class="target-pagi">
                                {{ $planning->target_shift_pagi ? number_format($planning->target_shift_pagi) : '' }}
                            </td>
                            <td class="target-siang">
                                {{ $planning->target_shift_siang ? number_format($planning->target_shift_siang) : '' }}
                            </td>
                            <td class="target-malam">
                                {{ $planning->target_shift_malam ? number_format($planning->target_shift_malam) : '' }}
                            </td>
                            <td rowspan="3">{{ $totalShift > 0 ? number_format($totalShift) : '' }}</td>
                </tr>

                {{-- ========== BARIS 2: PENCAPAIAN ========== --}}
                <tr class="row-pencapaian">
                    <td class="col-empty">PENCAPAIAN</td>

                    @for ($i = 7; $i <= 14; $i++)
                        <td class="shift-pagi">
                        </td>
                        @endfor

                        @for ($i = 15; $i <= 22; $i++)
                            <td class="shift-siang">
                            </td>
                            @endfor

                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <td class="shift-malam"></td>
                            @endforeach

                            <td class="target-pagi">0%</td>
                            <td class="target-siang">0%</td>
                            <td class="target-malam">0%</td>
                </tr>

                {{-- ========== BARIS 3: TRANSAKSI ========== --}}
                <tr class="row-transaksi">
                    <td class="col-empty">TRANSAKSI</td>

                    @for ($i = 7; $i <= 14; $i++)
                        <td class="shift-pagi">
                        </td>
                        @endfor

                        @for ($i = 15; $i <= 22; $i++)
                            <td class="shift-siang">
                            </td>
                            @endfor

                            @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                            <td class="shift-malam"></td>
                            @endforeach

                            <td class="target-pagi"></td>
                            <td class="target-siang"></td>
                            <td class="target-malam"></td>
                </tr>

                {{-- Garis pembatas antar planning --}}
                @if (!$loop->last)
                <tr class="row-pemisah">
                    <td colspan="37" style="height: 2px; background-color: #ccc;"></td>
                </tr>
                @endif
                @endforeach
            </tbody>

        </table>
        <!-- Footer -->
        <div class="footer-text-right">FORM-PPIC-3-12 REV01</div>
    </div>
    @endif

    <script>
        // Auto print jika mode = direct
        const printMode = "{{ $mode ?? '' }}";
        if (printMode === 'direct') {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>

</html>