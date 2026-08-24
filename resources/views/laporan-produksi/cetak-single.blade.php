<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produksi - Detail</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.8mm 1.2mm 0.8mm 1.2mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            max-width: 100%;
            text-align: left;
            color: #000;
        }

        /* Base Table Styles */
        table {
            border-collapse: collapse !important;
            width: 100%;
            margin-bottom: 0px;
        }

        th,
        td {
            border: 1px solid #000 !important;
            padding: 1.5px;
            vertical-align: middle;
        }

        /* Specific Table Overrides */
        .header-table,
        .production-table,
        .ppp-table,
        .sign-table {
            border: 1px solid #000 !important;
        }

        .ppp-table th,
        .ppp-table td {
            font-size: 8px !important;
            padding: 1.5px 2px !important;
        }

        .ppp-table tbody tr td {
            padding: 3px 2px !important;
        }

        .production-table td {
            padding: 1px 1.5px !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .shift-total td {
            background-color: #f2f2f2 !important;
            font-weight: bold !important;
        }

        .ppp-total td {
            background-color: #e9ecef !important;
            font-weight: bold !important;
        }

        .qr-signature-pdf {
            max-width: 30px !important;
            max-height: 30px !important;
            display: block;
            margin: 0 auto;
        }

        /* Header Specifics */
        .logo-cell {
            text-align: center;
            width: 18%;
        }

        .logo-cell img {
            height: 26px;
        }

        .instruction-cell {
            font-size: 6.5px;
            font-style: italic;
            text-align: center;
            width: 67%;
            padding: 1px 2px !important;
        }

        .dept-cell {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            background-color: #f8f9fa;
            width: 15%;
        }

        .info-row-table td {
            border: none !important;
            border-right: 1px solid #000 !important;
            padding: 0.8px 1.5px !important;
            font-size: 7px;
            font-weight: bold;
        }

        .info-row-table td:last-child {
            border-right: none !important;
        }

        /* Layout Table (Invisible Wrapper) */
        .layout-table {
            border: none !important;
            margin-top: 1px;
        }

        .layout-table>tr>td,
        .layout-table>tbody>tr>td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
        }

        .ng-header th {
            height: 16px;
            background-color: #f8f9fa;
            font-size: 7px;
        }

        .ppp-table tbody td {
            font-size: 7.5px !important;
        }

        .abnormal-box {
            border: 1px solid #000 !important;
            padding: 5px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 6px;
            line-height: 1.25;
            display: block;
        }
    </style>
</head>

<body>
    @php
    \Carbon\Carbon::setLocale('id');
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoPathPath ?? null)
                <img src="{{ $logoPathPath }}" alt="Logo">
                @endif
            </td>
            <td class="instruction-cell">
                <p style="margin: 0;">*(Sebelum mulai bekerja harap pastikan anda sudah melaksanakan On The Job Training untuk produk yang akan anda kerjakan hari ini dari leader di departemen anda)</p>
                <p style="margin: 0;">**(Sebagai bukti anda sudah bekerja dan sebagai garansi bagi customer, atas barang yang anda kerjakan)</p>
            </td>
            <td class="dept-cell">
                {{ ($data->planning->bagian ?? 'inject') === 'assy' ? 'ASSY' : 'INJECT' }}
            </td>
        </tr>
    </table>

    <!-- Informasi Part -->
    <table class="layout-table" style="width: 100%; margin-bottom: 0.5px;">
        <tr>
            <td style="width: 42%; vertical-align: top;">
                <table class="production-table" style="margin-bottom: 0;">
                    <tr>
                        <td style="width: 30%; background-color: #f8f9fa; font-weight: bold;">Hari, Tanggal</td>
                        <td colspan="3">: {{ $data->created_at->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">Nama Part</td>
                        <td colspan="3">: {{ $data->planning->part->nama_part ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">No. Part</td>
                        <td style="width: 35%;">: {{ $data->planning->part->no_parts ?? '-' }}</td>
                        <td style="width: 15%; background-color: #f8f9fa; font-weight: bold;">Type Part</td>
                        <td style="width: 20%;">: {{ $data->planning->part->type ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">No {{ ($data->planning->bagian ?? 'inject') === 'assy' ? 'Meja' : 'Mesin' }}</td>
                        <td colspan="3">: {{ ($data->planning->bagian ?? 'inject') === 'assy' ? ($data->planning->meja->nama_meja ?? '-') : ($data->planning->mesin->nama_mesin ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">Qty Planning</td>
                        <td colspan="3">: {{ number_format($data->planning->qty_planning_produksi ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">No. Lot Produksi</td>
                        <td colspan="3">: {{ $data->planning->lot_produksi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-weight: bold;">Cycle time Aktual</td>
                        <td colspan="3" style="padding: 0;">
                            <table style="width: 100%; border: none; margin: 0;">
                                <tr>
                                    <td style="border: none !important; border-right: 1px solid #000 !important; width: 33.3%; font-size: 7.5px;">Shift 1:</td>
                                    <td style="border: none !important; border-right: 1px solid #000 !important; width: 33.3%; font-size: 7.5px;">Shift 2:</td>
                                    <td style="border: none !important; width: 33.4%; font-size: 7.5px;">Shift 3:</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 1%; border: none !important;"></td>
            <td style="width: 57%; vertical-align: top;">
                <table class="production-table" style="margin-bottom: 0;">
                    <tr>
                        <td style="width: 25%; background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 1</td>
                        <td style="width: 25%;"></td>
                        <td style="width: 25%; background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 5</td>
                        <td style="width: 25%;"></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 2</td>
                        <td></td>
                        <td style="background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 6</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 3</td>
                        <td></td>
                        <td style="background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 7</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7.5px; font-weight: bold;">Nama Subpart 4</td>
                        <td></td>
                        <td colspan="2" rowspan="2" style="position: relative; vertical-align: bottom; text-align: right; padding: 2px;">
                            <div style="border: 1px solid #000; display: inline-block; padding: 1px 4px; font-size: 6.5px; font-weight: bold; margin-bottom: 2px; margin-right: 2px;">
                                01
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; font-size: 7px;">No Lot Subpart</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Persiapan Proses Produksi -->
    <div style="margin-top: 0.5px; margin-bottom: 0.3px; font-size: 8.5px; font-weight: bold; text-align: center;">PERSIAPAN PROSES PRODUKSI</div>
    <table class="ppp-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:20px; background-color: #f8f9fa;">No</th>
                <th rowspan="2" style="width:180px; background-color: #f8f9fa;">AKTIVITAS <small>(Beri tanda (<span style="font-family: 'DejaVu Sans', sans-serif;">&#10003;</span>) bila sudah dilaksanakan)</small></th>
                <th colspan="2" style="background-color: #f8f9fa;">Shift 1</th>
                <th colspan="2" style="background-color: #f8f9fa;">Shift 2</th>
                <th colspan="2" style="background-color: #f8f9fa;">Shift 3</th>
                <th rowspan="2" style="width:100px; background-color: #f8f9fa;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width:20px; background-color: #f8f9fa;">OP</th>
                <th style="width:20px; background-color: #f8f9fa;">Leader</th>
                <th style="width:20px; background-color: #f8f9fa;">OP</th>
                <th style="width:20px; background-color: #f8f9fa;">Leader</th>
                <th style="width:20px; background-color: #f8f9fa;">OP</th>
                <th style="width:20px; background-color: #f8f9fa;">Leader</th>
            </tr>
        </thead>
        <tbody>
            @php
            $items = ['Briefing dan tukar informasi', 'Kesiapan Produk WIP & Subpart', 'Kesiapan Packing', 'Meja proses tersedia, lampu penerangan menyala', 'Kelengkapan dokumen IK, Q-Point, dan Std Packing', 'Pengecekan 5R', 'Pemeriksaan kondisi subpart', 'Kesiapan Tag Label, Marker, dan Cutter', 'Ketersediaan limit sampel produk'];
            $pData = $persiapanData ?? ['Pagi' => [], 'Siang' => [], 'Malam' => []];
            @endphp
            @foreach($items as $index => $itemName)
            @php
            $pagiItem = $pData['Pagi'][$itemName] ?? null;
            $siangItem = $pData['Siang'][$itemName] ?? null;
            $malamItem = $pData['Malam'][$itemName] ?? null;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $itemName }}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $pagiItem && $pagiItem['oc'] ? '&#10003;' : '' !!}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $pagiItem && $pagiItem['lc'] ? '&#10003;' : '' !!}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $siangItem && $siangItem['oc'] ? '&#10003;' : '' !!}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $siangItem && $siangItem['lc'] ? '&#10003;' : '' !!}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $malamItem && $malamItem['oc'] ? '&#10003;' : '' !!}</td>
                <td class="text-center" style="font-family: 'DejaVu Sans', sans-serif;">{!! $malamItem && $malamItem['lc'] ? '&#10003;' : '' !!}</td>
                <td style="font-size: 5.5px;">
                    @if($pagiItem && $pagiItem['k']) P: {{ $pagiItem['k'] }} @endif
                    @if($siangItem && $siangItem['k']) S: {{ $siangItem['k'] }} @endif
                    @if($malamItem && $malamItem['k']) M: {{ $malamItem['k'] }} @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pencapaian Proses Produksi -->
    <div style="margin-top: 0.3px; margin-bottom: 0.3px; font-size: 8.5px; font-weight: bold; text-align: center;">PENCAPAIAN PROSES PRODUKSI</div>
    <table class="ppp-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:60px; background-color: #f8f9fa;">JAM</th>
                <th rowspan="2" style="width:22px; background-color: #f8f9fa;">TARGET</th>
                <th colspan="3" style="background-color: #f8f9fa;">Qty Output</th>
                <th rowspan="2" style="width:45px; background-color: #f8f9fa;">OPERATOR</th>
                <th colspan="7" style="background-color: #f8f9fa;">NG Subpart</th>
                <th rowspan="2" style="width:65px; background-color: #f8f9fa;">MASALAH</th>
                <th rowspan="2" style="width:65px; background-color: #f8f9fa;">TINDAKAN</th>
                <th rowspan="2" style="width:30px; background-color: #f8f9fa;">DOWNTIME</th>
                <th colspan="2" style="width:24px; background-color: #f8f9fa;">CHECKER</th>
            </tr>
            <tr>
                <th style="width:18px; background-color: #f8f9fa;">OK</th>
                <th style="width:18px; background-color: #f8f9fa;">NG</th>
                <th style="width:18px; background-color: #f8f9fa;">TOTAL</th>
                <th style="width:11px; background-color: #f8f9fa;">1</th>
                <th style="width:11px; background-color: #f8f9fa;">2</th>
                <th style="width:11px; background-color: #f8f9fa;">3</th>
                <th style="width:11px; background-color: #f8f9fa;">4</th>
                <th style="width:11px; background-color: #f8f9fa;">5</th>
                <th style="width:11px; background-color: #f8f9fa;">6</th>
                <th style="width:11px; background-color: #f8f9fa;">7</th>
                <th style="width:12px; background-color: #f8f9fa;">PRD</th>
                <th style="width:12px; background-color: #f8f9fa;">QC</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['pagi', 'siang', 'malam'] as $shiftKey)
            @php
            $shiftItems = $produksiPerJamData[$shiftKey] ?? [];
            $sTarget = 0; $sOk = 0; $sNg = 0;
            @endphp
            @foreach($shiftItems as $jam => $item)
            @php $sTarget += $item['target'] ?? 0; $sOk += $item['ok_count'] ?? 0; $sNg += $item['ng_part'] ?? 0; @endphp
            <tr>
                <td class="text-center">{{ $jam }}</td>
                <td class="text-center">{{ $item['target'] }}</td>
                <td class="text-center">{{ $item['ok_count'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_part'] ?: '' }}</td>
                <td class="text-center">{{ ($item['ok_count'] + $item['ng_part']) ?: '' }}</td>
                <td class="text-center">{{ $item['operator_code'] }}</td>
                <td class="text-center">{{ $item['ng_subpart_1'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_2'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_3'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_4'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_5'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_6'] ?: '' }}</td>
                <td class="text-center">{{ $item['ng_subpart_7'] ?: '' }}</td>
                <td style="font-size: 5.5px;">{{ $item['masalah'] }}</td>
                <td style="font-size: 5.5px;">{{ $item['tindakan'] }}</td>
                <td class="text-center"></td>
                <td class="text-center">{{ $item['checker_prd'] }}</td>
                <td class="text-center">{{ $item['checker_qc'] }}</td>
            </tr>
            @endforeach
            <tr class="shift-total">
                <td class="text-center">SHIFT {{ $loop->iteration }}</td>
                <td class="text-center">{{ $sTarget }}</td>
                <td class="text-center">{{ $sOk }}</td>
                <td class="text-center">{{ $sNg }}</td>
                <td class="text-center">{{ $sOk + $sNg }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
            <tr class="ppp-total">
                <td style="text-align: center; font-weight: bold;">TOTAL</td>
                <td class="text-center font-bold">{{ $grandTotal['target'] }}</td>
                <td class="text-center font-bold">{{ $grandTotal['ok'] }}</td>
                <td class="text-center font-bold">{{ $grandTotal['ng'] }}</td>
                <td class="text-center font-bold">{{ $grandTotal['ok'] + $grandTotal['ng'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Layout Table for NG Part Types and Signature -->
    <table class="layout-table">
        <tr>
            <td style="width: 58%;">
                <table class="ppp-table" style="margin-bottom: 0;">
                    <thead>
                        <tr class="ng-header">
                            <th style="width: 22%;">Jenis NG</th>
                            <th style="width: 9%;">Shift 1</th>
                            <th style="width: 9%;">Shift 2</th>
                            <th style="width: 9%;">Shift 3</th>
                            <th style="width: 22%;">Jenis NG</th>
                            <th style="width: 9%;">Shift 1</th>
                            <th style="width: 9%;">Shift 2</th>
                            <th style="width: 9%;">Shift 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $allTypes = isset($ngPartTypesData) && $ngPartTypesData['all_types'] ? $ngPartTypesData['all_types']->values() : collect([]);
                        $shift1Data = $ngPartTypesData['by_shift']['Shift 1'] ?? [];
                        $shift2Data = $ngPartTypesData['by_shift']['Shift 2'] ?? [];
                        $shift3Data = $ngPartTypesData['by_shift']['Shift 3'] ?? [];
                        $chunks = $allTypes->count() > 0 ? $allTypes->chunk(max(1, ceil($allTypes->count() / 2))) : collect([]);
                        $leftColumn = $chunks->count() > 0 && $chunks->get(0) ? $chunks->get(0)->values() : collect([]);
                        $rightColumn = $chunks->count() > 1 && $chunks->get(1) ? $chunks->get(1)->values() : collect([]);
                        $maxRows = max($leftColumn->count(), $rightColumn->count());
                        @endphp
                        @for($i = 0; $i < $maxRows; $i++)
                            <tr>
                            @php $leftItem = $leftColumn->get($i); $rightItem = $rightColumn->get($i); @endphp
                            @if($leftItem)
                            @php $s1 = 0; $s2 = 0; $s3 = 0;
                            foreach($shift1Data as $item) { if($item['name'] === $leftItem->name) { $s1 = $item['quantity']; break; } }
                            foreach($shift2Data as $item) { if($item['name'] === $leftItem->name) { $s2 = $item['quantity']; break; } }
                            foreach($shift3Data as $item) { if($item['name'] === $leftItem->name) { $s3 = $item['quantity']; break; } }
                            @endphp
                            <td>{{ $leftItem->name }}</td>
                            <td class="text-center">{{ $s1 ?: '' }}</td>
                            <td class="text-center">{{ $s2 ?: '' }}</td>
                            <td class="text-center">{{ $s3 ?: '' }}</td>
                            @else
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            @endif
                            @if($rightItem)
                            @php $s1 = 0; $s2 = 0; $s3 = 0;
                            foreach($shift1Data as $item) { if($item['name'] === $rightItem->name) { $s1 = $item['quantity']; break; } }
                            foreach($shift2Data as $item) { if($item['name'] === $rightItem->name) { $s2 = $item['quantity']; break; } }
                            foreach($shift3Data as $item) { if($item['name'] === $rightItem->name) { $s3 = $item['quantity']; break; } }
                            @endphp
                            <td>{{ $rightItem->name }}</td>
                            <td class="text-center">{{ $s1 ?: '' }}</td>
                            <td class="text-center">{{ $s2 ?: '' }}</td>
                            <td class="text-center">{{ $s3 ?: '' }}</td>
                            @else
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            @endif
        </tr>
        @endfor
        </tbody>
    </table>
    </td>
    <td style="width: 8px;">&nbsp;</td>
    <td style="width: 17%;">
        <div style="height: 8px;">&nbsp;</div>
        <div class="abnormal-box">
            JIKA TERJADI<br>ABNORMAL WAJIB<br>SEGERA LAPOR<br>PADA LEADER ATAU<br>BAGIAN TERKAIT<br>UNTUK DITINDAK<br>LANJUTI!!!
        </div>
    </td>
    <td style="width: 8px;">&nbsp;</td>
    <td style="width: 140px;">
        <table class="sign-table">
            <tr>
                <th colspan="3" style="background-color: #f8f9fa; font-size: 6.5px;">Operator</th>
            </tr>
            <tr>
                <th style="width: 33.33%; font-size: 6px;">Shift 1</th>
                <th style="width: 33.33%; font-size: 6px;">Shift 2</th>
                <th style="width: 33.33%; font-size: 6px;">Shift 3</th>
            </tr>
            <tr>
                @php
                $s1Ops = $operatorsByShift['shift_1'] ?? []; $s2Ops = $operatorsByShift['shift_2'] ?? []; $s3Ops = $operatorsByShift['shift_3'] ?? [];
                $s1Qr = $operatorsQrByShift['shift_1'] ?? []; $s2Qr = $operatorsQrByShift['shift_2'] ?? []; $s3Qr = $operatorsQrByShift['shift_3'] ?? [];
                @endphp
                <td style="height: 28px; text-align: center;">
                    @foreach(array_slice($s1Ops, 0, 3) as $idx => $op)
                    @if(isset($s1Qr[$idx]) && $s1Qr[$idx]) <img src="{{ $s1Qr[$idx] }}" class="qr-signature-pdf"> @endif
                    <div style="font-size: 4.5px; font-weight: bold;">{{ $op['nama'] }}</div>
                    @endforeach
                </td>
                <td style="height: 28px; text-align: center;">
                    @foreach(array_slice($s2Ops, 0, 3) as $idx => $op)
                    @if(isset($s2Qr[$idx]) && $s2Qr[$idx]) <img src="{{ $s2Qr[$idx] }}" class="qr-signature-pdf"> @endif
                    <div style="font-size: 4.5px; font-weight: bold;">{{ $op['nama'] }}</div>
                    @endforeach
                </td>
                <td style="height: 28px; text-align: center;">
                    @foreach(array_slice($s3Ops, 0, 3) as $idx => $op)
                    @if(isset($s3Qr[$idx]) && $s3Qr[$idx]) <img src="{{ $s3Qr[$idx] }}" class="qr-signature-pdf"> @endif
                    <div style="font-size: 4.5px; font-weight: bold;">{{ $op['nama'] }}</div>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th colspan="3" style="background-color: #f8f9fa; font-size: 6.5px;">Shift Leader</th>
            </tr>
            <tr>
                <th style="font-size: 6px;">Shift 1</th>
                <th style="font-size: 6px;">Shift 2</th>
                <th style="font-size: 6px;">Shift 3</th>
            </tr>
            <tr>
                @php
                $leaders = [['l' => $leadersByShift['shift_1'] ?? null, 'q' => $leadersQrByShift['shift_1'] ?? null], ['l' => $leadersByShift['shift_2'] ?? null, 'q' => $leadersQrByShift['shift_2'] ?? null], ['l' => $leadersByShift['shift_3'] ?? null, 'q' => $leadersQrByShift['shift_3'] ?? null]];
                @endphp
                @foreach($leaders as $ld)
                <td style="height: 25px; text-align: center;">
                    @if($ld['l'])
                    @if($ld['q']) <img src="{{ $ld['q'] }}" class="qr-signature-pdf"> @endif
                    <div style="font-size: 4.5px; font-weight: bold;">{{ $ld['l']->nama }}</div>
                    @else &nbsp; @endif
                </td>
                @endforeach
            </tr>
        </table>
    </td>
    </tr>
    </table>

    <!-- Notes Section -->
    <div style="font-size: 5.5px; margin-top: 0; line-height: 1.1;">
        <p style="margin: 0;"><strong>Note: (*Apabila terdapat pergantian material, anda diwajibkan untuk memastikan sudah dilaksanakan cleaning hooper atau belum)</strong></p>
        <p style="margin: 0;"><strong>(**5R, Cek biji plastik tidak boleh berserakan diarea mesin, Cek Debu di Bodi Mesin, Cek Grease di sela dan Roda Pintu)</strong></p>
    </div>
</body>

</html>