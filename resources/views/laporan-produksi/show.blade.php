@extends('laporan-produksi.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Detail Laporan Produksi #{{ $data->id }}</h4>
        <a href="{{ route('laporan-produksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Detail</h5>
                <a href="{{ route('laporan-produksi.cetak-single', $data->id) }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-print"></i> Cetak PDF
                </a>
            </div>
        </div>
        <div class="card-body" style="overflow-x: auto;">
            <style>
                /* Adapted styles from cetak-single.blade.php */
                .production-table {
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid black;
                    font-size: 12px;
                    margin-bottom: 10px;
                }

                .production-table th,
                .production-table td {
                    border: 1px solid black;
                    padding: 5px;
                }

                .ppp-table {
                    width: 100%;
                    border-spacing: 0 !important;
                    border-collapse: collapse !important;
                    border: 1px solid black;
                    font-size: 10px !important;
                    margin-bottom: 10px;
                }

                .ppp-table th,
                .ppp-table td {
                    border: 1px solid black !important;
                    padding: 3px !important;
                    margin: 0 !important;
                    font-size: 10px !important;
                }

                .ppp-title {
                    font-weight: bold;
                    margin-top: 10px;
                    margin-bottom: 5px;
                    font-size: 14px;
                    text-decoration: underline;
                }

                .text-center {
                    text-align: center;
                }

                .ppp-col-right {
                    text-align: right;
                }

                .ppp-col-center {
                    text-align: center;
                }

                .sign-table {
                    width: 100%;
                    table-layout: fixed;
                    border-collapse: collapse;
                    border: 1px solid black;
                }

                .sign-table td,
                .sign-table th {
                    border: 1px solid black;
                    padding: 5px;
                    text-align: center;
                }

                .qr-signature-pdf {
                    max-width: 50px;
                    max-height: 50px;
                    width: 50px;
                    height: 50px;
                    object-fit: contain;
                    display: block;
                    margin: 0 auto;
                }

                .sign-line {
                    height: 40px;
                    width: 40px;
                    border: 1px solid #ddd;
                    margin: 0 auto;
                    background-color: #f9f9f9;
                }
            </style>

            <div class="header mb-3 text-center">
                <img src="{{ asset('images/logomwthijau.png') }}" alt="PT. MADA WIKRI TUNGGAL" class="header-logo" style="height: 60px; margin-bottom: 10px;">
                <h3 style="font-size: 20px; margin-bottom: 5px;">LAPORAN PRODUKSI - DETAIL</h3>
                <h4 style="font-size: 14px;">Tanggal: {{ $data->created_at->format('d-m-Y') }}</h4>
            </div>

            <!-- Informasi Part -->
            <table class="production-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 10%;">Bagian</th>
                        <th style="width: 20%;">{{ ($data->planning->bagian ?? 'inject') === 'assy' ? 'Meja' : 'Mesin' }}</th>
                        <th style="width: 25%;">Part Code</th>
                        <th style="width: 25%;">Part Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>{{ $data->created_at->format('d-m-Y') }}</td>
                        <td class="text-center">
                            @if(($data->planning->bagian ?? 'inject') === 'assy')
                            <strong>ASSY</strong>
                            @else
                            <strong>INJECT</strong>
                            @endif
                        </td>
                        <td>
                            @if(($data->planning->bagian ?? 'inject') === 'assy')
                            {{ $data->planning->meja->nama_meja ?? '-' }}
                            @else
                            {{ $data->planning->mesin->nama_mesin ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $data->planning->part->no_parts ?? '-' }}</td>
                        <td>{{ $data->planning->part->nama_part ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Persiapan Proses Produksi -->
            <div class="ppp-title">PERSIAPAN PROSES PRODUKSI</div>
            <table class="ppp-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:30px;">No Ex.</th>
                        <th rowspan="2">AKTIVITAS<br><small>(Beri tanda (✓) apabila sudah melaksanakan kegiatan tersebut)</small></th>
                        <th colspan="2" style="width:80px;">Shift 1</th>
                        <th colspan="2" style="width:80px;">Shift 2</th>
                        <th colspan="2" style="width:80px;">Shift 3</th>
                        <th rowspan="2" style="width:200px;">KETERANGAN<br><small>(Beri penjelasan bila tidak terdapat kesesuaian dan segera lapor ke leader)</small></th>
                    </tr>
                    <tr>
                        <th style="width:40px;">OP</th>
                        <th style="width:40px;">Leader</th>
                        <th style="width:40px;">OP</th>
                        <th style="width:40px;">Leader</th>
                        <th style="width:40px;">OP</th>
                        <th style="width:40px;">Leader</th>
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
                    @foreach($items as $index => $item)
                    @php
                    $persiapanData = $persiapanData ?? ['Pagi' => [], 'Siang' => [], 'Malam' => []];
                    $pagiData = $persiapanData['Pagi'] ?? [];
                    $siangData = $persiapanData['Siang'] ?? [];
                    $malamData = $persiapanData['Malam'] ?? [];

                    $pagiItem = collect($pagiData)->firstWhere('item_name', $item);
                    $siangItem = collect($siangData)->firstWhere('item_name', $item);
                    $malamItem = collect($malamData)->firstWhere('item_name', $item);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item }}</td>
                        <!-- Shift 1 -->
                        <td class="text-center">{{ $pagiItem && $pagiItem['operator_checked'] ? '✓' : '' }}</td>
                        <td class="text-center">{{ $pagiItem && $pagiItem['leader_checked'] ? '✓' : '' }}</td>
                        <!-- Shift 2 -->
                        <td class="text-center">{{ $siangItem && $siangItem['operator_checked'] ? '✓' : '' }}</td>
                        <td class="text-center">{{ $siangItem && $siangItem['leader_checked'] ? '✓' : '' }}</td>
                        <!-- Shift 3 -->
                        <td class="text-center">{{ $malamItem && $malamItem['operator_checked'] ? '✓' : '' }}</td>
                        <td class="text-center">{{ $malamItem && $malamItem['leader_checked'] ? '✓' : '' }}</td>
                        <!-- Keterangan -->
                        <td>
                            @if($pagiItem && $pagiItem['keterangan'])
                            <small>Pagi: {{ $pagiItem['keterangan'] }}</small><br>
                            @endif
                            @if($siangItem && $siangItem['keterangan'])
                            <small>Siang: {{ $siangItem['keterangan'] }}</small><br>
                            @endif
                            @if($malamItem && $malamItem['keterangan'])
                            <small>Malam: {{ $malamItem['keterangan'] }}</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Template Pencapaian Proses Produksi -->
            <div class="ppp-title">PENCAPAIAN PROSES PRODUKSI</div>
            <table class="ppp-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:100px;">JAM TERSEDIA</th>
                        <th rowspan="2" style="width:50px;">TARGET</th>
                        <th colspan="3">Qty Output Produksi</th>
                        <th rowspan="2" style="width:60px;">Kode Operator</th>
                        <th colspan="7">NG Subpart</th>
                        <th rowspan="2" style="width:100px;">MASALAH</th>
                        <th rowspan="2" style="width:100px;">TINDAKAN</th>
                        <th colspan="2" style="width:50px;">Checker</th>
                    </tr>
                    <tr>
                        <th style="width:40px;">OK</th>
                        <th style="width:40px;">NG PART</th>
                        <th style="width:40px;">TOTAL</th>
                        <th style="width:20px;">1</th>
                        <th style="width:20px;">2</th>
                        <th style="width:20px;">3</th>
                        <th style="width:20px;">4</th>
                        <th style="width:20px;">5</th>
                        <th style="width:20px;">6</th>
                        <th style="width:20px;">7</th>
                        <th style="width:25px;">PRD</th>
                        <th style="width:25px;">QC</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $rowsShift1 = ['07.00 - 08.00', '08.00 - 09.00', '09.00 - 10.00', '10.00 - 11.00', '11.00 - 12.00', '12.00 - 13.00', '13.00 - 14.00', '14.00 - 15.00'];
                    $rowsShift2 = ['15.00 - 16.00', '16.00 - 17.00', '17.00 - 18.00', '18.00 - 19.00', '19.00 - 20.00', '20.00 - 21.00', '21.00 - 22.00', '22.00 - 23.00'];
                    $rowsShift3 = ['23.00 - 24.00', '24.00 - 01.00', '01.00 - 02.00', '02.00 - 03.00', '03.00 - 04.00', '04.00 - 05.00', '05.00 - 06.00', '06.00 - 07.00'];
                    @endphp

                    <!-- Shift 1 -->
                    @foreach ($rowsShift1 as $lbl)
                    @php
                    $jamData = $produksiPerJamData['pagi'][$lbl] ?? null;
                    $okCount = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                    $ngPart = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                    $operatorCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                    $total = $okCount + $ngPart;
                    $targetPerJam = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr>
                        <td class="ppp-col-center">{{ $lbl }}</td>
                        <td class="ppp-col-right">{{ number_format($targetPerJam, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ $okCount > 0 ? number_format($okCount, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $ngPart > 0 ? number_format($ngPart, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $total > 0 ? number_format($total, 0, ',', '.') : '' }}</td>
                        <td>{{ $operatorCode }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_1']) && $jamData['ng_subpart_1'] > 0 ? $jamData['ng_subpart_1'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_2']) && $jamData['ng_subpart_2'] > 0 ? $jamData['ng_subpart_2'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_3']) && $jamData['ng_subpart_3'] > 0 ? $jamData['ng_subpart_3'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_4']) && $jamData['ng_subpart_4'] > 0 ? $jamData['ng_subpart_4'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_5']) && $jamData['ng_subpart_5'] > 0 ? $jamData['ng_subpart_5'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_6']) && $jamData['ng_subpart_6'] > 0 ? $jamData['ng_subpart_6'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_7']) && $jamData['ng_subpart_7'] > 0 ? $jamData['ng_subpart_7'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['masalah']) ? $jamData['masalah'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['tindakan']) ? $jamData['tindakan'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_prd']) ? $jamData['checker_prd'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_qc']) ? $jamData['checker_qc'] : '' }}</td>
                    </tr>
                    @endforeach
                    @php $totalShift1 = ($totalPerShift['pagi']['ok'] ?? 0) + ($totalPerShift['pagi']['ng'] ?? 0); @endphp
                    <tr class="bg-light font-weight-bold">
                        <td>SHIFT 1</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['pagi']['target'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['pagi']['ok'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ ($totalPerShift['pagi']['ng'] ?? 0) > 0 ? number_format($totalPerShift['pagi']['ng'] ?? 0, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $totalShift1 > 0 ? number_format($totalShift1, 0, ',', '.') : '' }}</td>
                        <td colspan="12"></td>
                    </tr>

                    <!-- Shift 2 -->
                    @foreach ($rowsShift2 as $lbl)
                    @php
                    $jamData = $produksiPerJamData['siang'][$lbl] ?? null;
                    $okCount = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                    $ngPart = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                    $operatorCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                    $total = $okCount + $ngPart;
                    $targetPerJam = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr>
                        <td class="ppp-col-center">{{ $lbl }}</td>
                        <td class="ppp-col-right">{{ number_format($targetPerJam, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ $okCount > 0 ? number_format($okCount, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $ngPart > 0 ? number_format($ngPart, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $total > 0 ? number_format($total, 0, ',', '.') : '' }}</td>
                        <td>{{ $operatorCode }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_1']) && $jamData['ng_subpart_1'] > 0 ? $jamData['ng_subpart_1'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_2']) && $jamData['ng_subpart_2'] > 0 ? $jamData['ng_subpart_2'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_3']) && $jamData['ng_subpart_3'] > 0 ? $jamData['ng_subpart_3'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_4']) && $jamData['ng_subpart_4'] > 0 ? $jamData['ng_subpart_4'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_5']) && $jamData['ng_subpart_5'] > 0 ? $jamData['ng_subpart_5'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_6']) && $jamData['ng_subpart_6'] > 0 ? $jamData['ng_subpart_6'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_7']) && $jamData['ng_subpart_7'] > 0 ? $jamData['ng_subpart_7'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['masalah']) ? $jamData['masalah'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['tindakan']) ? $jamData['tindakan'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_prd']) ? $jamData['checker_prd'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_qc']) ? $jamData['checker_qc'] : '' }}</td>
                    </tr>
                    @endforeach
                    @php $totalShift2 = ($totalPerShift['siang']['ok'] ?? 0) + ($totalPerShift['siang']['ng'] ?? 0); @endphp
                    <tr class="bg-light font-weight-bold">
                        <td>SHIFT 2</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['siang']['target'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['siang']['ok'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ ($totalPerShift['siang']['ng'] ?? 0) > 0 ? number_format($totalPerShift['siang']['ng'] ?? 0, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $totalShift2 > 0 ? number_format($totalShift2, 0, ',', '.') : '' }}</td>
                        <td colspan="12"></td>
                    </tr>

                    <!-- Shift 3 -->
                    @foreach ($rowsShift3 as $lbl)
                    @php
                    $jamData = $produksiPerJamData['malam'][$lbl] ?? null;
                    $okCount = isset($jamData['ok_count']) ? (int)$jamData['ok_count'] : 0;
                    $ngPart = isset($jamData['ng_part']) ? (int)$jamData['ng_part'] : 0;
                    $operatorCode = isset($jamData['operator_code']) ? (string)$jamData['operator_code'] : '';
                    $total = $okCount + $ngPart;
                    $targetPerJam = $jamData && isset($jamData['target']) ? (int)$jamData['target'] : 0;
                    @endphp
                    <tr>
                        <td class="ppp-col-center">{{ $lbl }}</td>
                        <td class="ppp-col-right">{{ number_format($targetPerJam, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ $okCount > 0 ? number_format($okCount, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $ngPart > 0 ? number_format($ngPart, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $total > 0 ? number_format($total, 0, ',', '.') : '' }}</td>
                        <td>{{ $operatorCode }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_1']) && $jamData['ng_subpart_1'] > 0 ? $jamData['ng_subpart_1'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_2']) && $jamData['ng_subpart_2'] > 0 ? $jamData['ng_subpart_2'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_3']) && $jamData['ng_subpart_3'] > 0 ? $jamData['ng_subpart_3'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_4']) && $jamData['ng_subpart_4'] > 0 ? $jamData['ng_subpart_4'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_5']) && $jamData['ng_subpart_5'] > 0 ? $jamData['ng_subpart_5'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_6']) && $jamData['ng_subpart_6'] > 0 ? $jamData['ng_subpart_6'] : '' }}</td>
                        <td class="ppp-col-right">{{ $jamData && isset($jamData['ng_subpart_7']) && $jamData['ng_subpart_7'] > 0 ? $jamData['ng_subpart_7'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['masalah']) ? $jamData['masalah'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['tindakan']) ? $jamData['tindakan'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_prd']) ? $jamData['checker_prd'] : '' }}</td>
                        <td>{{ $jamData && isset($jamData['checker_qc']) ? $jamData['checker_qc'] : '' }}</td>
                    </tr>
                    @endforeach
                    @php
                    $totalShift3 = ($totalPerShift['malam']['ok'] ?? 0) + ($totalPerShift['malam']['ng'] ?? 0);
                    $grandTotalProduksi = ($grandTotal['ok'] ?? 0) + ($grandTotal['ng'] ?? 0);
                    @endphp
                    <tr class="bg-light font-weight-bold">
                        <td>SHIFT 3</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['malam']['target'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ number_format($totalPerShift['malam']['ok'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ ($totalPerShift['malam']['ng'] ?? 0) > 0 ? number_format($totalPerShift['malam']['ng'] ?? 0, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $totalShift3 > 0 ? number_format($totalShift3, 0, ',', '.') : '' }}</td>
                        <td colspan="12"></td>
                    </tr>
                    <tr class="bg-dark text-white font-weight-bold">
                        <td>TOTAL</td>
                        <td class="ppp-col-right">{{ number_format($grandTotal['target'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ number_format($grandTotal['ok'] ?? 0, 0, ',', '.') }}</td>
                        <td class="ppp-col-right">{{ ($grandTotal['ng'] ?? 0) > 0 ? number_format($grandTotal['ng'] ?? 0, 0, ',', '.') : '' }}</td>
                        <td class="ppp-col-right">{{ $grandTotalProduksi > 0 ? number_format($grandTotalProduksi, 0, ',', '.') : '' }}</td>
                        <td colspan="12"></td>
                    </tr>
                </tbody>
            </table>

            <!-- NG Part Types dan Tanda Tangan -->
            <div class="row">
                <div class="col-md-9">
                    @if(isset($ngPartTypesData) && $ngPartTypesData['all_types'] && $ngPartTypesData['all_types']->count() > 0)
                    <div class="ppp-title">NG Part Types</div>
                    <table class="ppp-table" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width:20%;">NG Part</th>
                                <th rowspan="2" style="width:10%;">Shift 1</th>
                                <th rowspan="2" style="width:10%;">Shift 2</th>
                                <th rowspan="2" style="width:10%;">Shift 3</th>
                                <th rowspan="2" style="width:20%;">NG Part</th>
                                <th rowspan="2" style="width:10%;">Shift 1</th>
                                <th rowspan="2" style="width:10%;">Shift 2</th>
                                <th rowspan="2" style="width:10%;">Shift 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $shift1Data = $ngPartTypesData['by_shift']['Shift 1'] ?? [];
                            $shift2Data = $ngPartTypesData['by_shift']['Shift 2'] ?? [];
                            $shift3Data = $ngPartTypesData['by_shift']['Shift 3'] ?? [];
                            $allNgParts = $ngPartTypesData['all_types'];
                            $halfCount = ceil($allNgParts->count() / 2);
                            $leftColumn = $allNgParts->take($halfCount)->values()->all();
                            $rightColumn = $allNgParts->skip($halfCount)->values()->all();
                            $maxRows = max(count($leftColumn), count($rightColumn));
                            @endphp
                            @for($i = 0; $i < $maxRows; $i++)
                                <tr>
                                <!-- Left Column -->
                                @if(isset($leftColumn[$i]))
                                @php
                                $ngPart = $leftColumn[$i];
                                $shift1Qty = 0; $shift2Qty = 0; $shift3Qty = 0;
                                foreach($shift1Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift1Qty = $item['quantity'] ?? 0; break; } }
                                foreach($shift2Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift2Qty = $item['quantity'] ?? 0; break; } }
                                foreach($shift3Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift3Qty = $item['quantity'] ?? 0; break; } }
                                @endphp
                                <td>{{ $ngPart->name ?? '' }}</td>
                                <td class="text-center">{{ $shift1Qty > 0 ? $shift1Qty : '' }}</td>
                                <td class="text-center">{{ $shift2Qty > 0 ? $shift2Qty : '' }}</td>
                                <td class="text-center">{{ $shift3Qty > 0 ? $shift3Qty : '' }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif

                                <!-- Right Column -->
                                @if(isset($rightColumn[$i]))
                                @php
                                $ngPart = $rightColumn[$i];
                                $shift1Qty = 0; $shift2Qty = 0; $shift3Qty = 0;
                                foreach($shift1Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift1Qty = $item['quantity'] ?? 0; break; } }
                                foreach($shift2Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift2Qty = $item['quantity'] ?? 0; break; } }
                                foreach($shift3Data as $item) { if(isset($item['name']) && $item['name'] === $ngPart->name) { $shift3Qty = $item['quantity'] ?? 0; break; } }
                                @endphp
                                <td>{{ $ngPart->name ?? '' }}</td>
                                <td class="text-center">{{ $shift1Qty > 0 ? $shift1Qty : '' }}</td>
                                <td class="text-center">{{ $shift2Qty > 0 ? $shift2Qty : '' }}</td>
                                <td class="text-center">{{ $shift3Qty > 0 ? $shift3Qty : '' }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif
                                </tr>
                                @endfor
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="ppp-title">Tanda Tangan</div>
                    <table class="sign-table">
                        <tr>
                            <th colspan="3">Operator</th>
                        </tr>
                        <tr>
                            <th>Shift 1</th>
                            <th>Shift 2</th>
                            <th>Shift 3</th>
                        </tr>
                        <tr>
                            @php
                            $shift1Operators = $operatorsByShift['shift_1'] ?? [];
                            $shift2Operators = $operatorsByShift['shift_2'] ?? [];
                            $shift3Operators = $operatorsByShift['shift_3'] ?? [];
                            $shift1Qr = $operatorsQrByShift['shift_1'] ?? [];
                            $shift2Qr = $operatorsQrByShift['shift_2'] ?? [];
                            $shift3Qr = $operatorsQrByShift['shift_3'] ?? [];
                            $maxOperators = 3;
                            @endphp
                            <td class="qr-cell">
                                @if(count($shift1Operators) > 0)
                                @foreach(array_slice($shift1Operators, 0, $maxOperators) as $index => $operator)
                                @if(isset($shift1Qr[$index]) && $shift1Qr[$index])
                                <img src="{!! $shift1Qr[$index] !!}" alt="QR Operator" class="qr-signature-pdf">
                                @else
                                <div class="sign-line"></div>
                                @endif
                                <div style="font-size: 10px;">{{ $operator['nama'] ?? '-' }}</div>
                                @endforeach
                                @else
                                <div class="sign-line"></div>
                                @endif
                            </td>
                            <td class="qr-cell">
                                @if(count($shift2Operators) > 0)
                                @foreach(array_slice($shift2Operators, 0, $maxOperators) as $index => $operator)
                                @if(isset($shift2Qr[$index]) && $shift2Qr[$index])
                                <img src="{!! $shift2Qr[$index] !!}" alt="QR Operator" class="qr-signature-pdf">
                                @else
                                <div class="sign-line"></div>
                                @endif
                                <div style="font-size: 10px;">{{ $operator['nama'] ?? '-' }}</div>
                                @endforeach
                                @else
                                <div class="sign-line"></div>
                                @endif
                            </td>
                            <td class="qr-cell">
                                @if(count($shift3Operators) > 0)
                                @foreach(array_slice($shift3Operators, 0, $maxOperators) as $index => $operator)
                                @if(isset($shift3Qr[$index]) && $shift3Qr[$index])
                                <img src="{!! $shift3Qr[$index] !!}" alt="QR Operator" class="qr-signature-pdf">
                                @else
                                <div class="sign-line"></div>
                                @endif
                                <div style="font-size: 10px;">{{ $operator['nama'] ?? '-' }}</div>
                                @endforeach
                                @else
                                <div class="sign-line"></div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3">Shift Leader</th>
                        </tr>
                        <tr>
                            <th>Shift 1</th>
                            <th>Shift 2</th>
                            <th>Shift 3</th>
                        </tr>
                        <tr>
                            @php
                            $shiftLeaders = [
                            ['leader' => $leadersByShift['shift_1'] ?? null, 'qr' => $leadersQrByShift['shift_1'] ?? null],
                            ['leader' => $leadersByShift['shift_2'] ?? null, 'qr' => $leadersQrByShift['shift_2'] ?? null],
                            ['leader' => $leadersByShift['shift_3'] ?? null, 'qr' => $leadersQrByShift['shift_3'] ?? null],
                            ];
                            @endphp
                            @foreach($shiftLeaders as $shiftLeader)
                            <td class="qr-cell">
                                @if($shiftLeader['leader'])
                                @if($shiftLeader['qr'])
                                <img src="{!! $shiftLeader['qr'] !!}" alt="QR Leader" class="qr-signature-pdf">
                                @else
                                <div class="sign-line"></div>
                                @endif
                                <div style="font-size: 10px;">{{ $shiftLeader['leader']->nama ?? '-' }}</div>
                                @else
                                <div class="sign-line"></div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Notes Section -->
            @if(isset($ngPartTypesData) && $ngPartTypesData['all_types'] && $ngPartTypesData['all_types']->count() > 0)
            <div class="mt-3" style="font-size: 11px;">
                <p class="mb-0"><strong>Note: (*Apabila terdapat pergantian material, anda diwajibkan untuk memastikan sudah dilaksanakan cleaning hooper atau belum)</strong></p>
                <p class="mb-0"><strong>(**5R, Cek biji plastik tidak boleh berserakan diarea mesin, Cek Debu di Bodi Mesin, Cek Grease di sela dan Roda Pintu)</strong></p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection