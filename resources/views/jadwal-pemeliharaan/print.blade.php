<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pemeliharaan Mesin {{ $tahun }}</title>
    <style>
        /* CSS KHUSUS CETAK - STANDAR INDUSTRI */
        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }

        body {
            font-family: "Arial Narrow", Arial, sans-serif;
            font-size: 7.5pt;
            margin: 0;
            padding: 0;
            background-color: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Header Laporan Resmi PT Mada Wikri Tunggal */
        .report-header {
            width: 100%;
            border: 1.5px solid #000;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .report-header td {
            border: 1.5px solid #000;
            padding: 4px;
            vertical-align: middle;
        }

        .logo-section {
            text-align: center;
            width: 20%;
        }

        .title-section {
            text-align: center;
            width: 55%;
            font-size: 13pt;
            font-weight: bold;
        }

        .meta-section {
            width: 25%;
            padding: 0 !important;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            border: none;
            border-bottom: 0.5px solid #000;
            padding: 1px 4px;
            font-size: 6.5pt;
            text-align: left;
        }

        .meta-table tr:last-child td {
            border-bottom: none;
        }

        /* Grid Tabel Utama */
        .table-pm {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto; /* Biarkan browser menghitung otomatis proporsi isi */
        }

        .table-pm th, .table-pm td {
            border: 1px solid #000;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            height: 18px;
            word-wrap: break-word; /* Pisahkan kata jika panjang */
            word-break: break-all; /* Pemecahan paksa untuk teks nyambung spt tanggal */
        }

        .table-pm thead th {
            font-weight: bold;
            font-size: 6.5pt;
            background-color: #f0f0f0 !important;
        }

        /* Dimensi Kolom Khusus */
        .col-no { width: 15px; }
        .col-nama { width: 100px; text-align: left !important; padding-left: 3px !important; font-size: 7pt !important; }
        .col-nomor { width: 30px; }
        .col-target { width: 35px; font-weight: bold; font-size: 5.5pt; }

        /* Pewarnaan */
        .row-actual { 
            background-color: #ffff00 !important; 
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Kotak Plan Hijau */
        .box-plan {
            background-color: #006400 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: 5.5pt;
            border: 0.5px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 90%;
            height: 90%;
            margin: 0 auto;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .actual-text {
            font-weight: bold;
            font-size: 5.5pt;
            color: #000;
            display: block;
        }

        /* Tombol Cetak (Hanya Muncul di Layar) */
        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        @media print {
            .no-print-btn { display: none !important; }
            body { 
                /* Jangan paksa zoom: 100%, biarkan browser 'Fit to Page' secara natural */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <button class="no-print-btn" onclick="window.print()">KLIK UNTUK CETAK SEKARANG</button>

    <div class="container">
        <!-- HEADER RESMI -->
        <table class="report-header">
            <tr>
                <td class="logo-section">
                    <div style="font-weight: bold; font-size: 10pt;">PT MADA WIKRI TUNGGAL</div>
                    <div style="font-size: 7pt; font-weight: bold;">Dept. Repair And Maintenance</div>
                </td>
                <td class="title-section">
                    JADWAL PEMELIHARAAN MESIN
                </td>
                <td class="meta-section">
                    <table class="meta-table">
                        <tr>
                            <td width="40%">DIVISI</td>
                            <td>: ALL</td>
                        </tr>
                        <tr>
                            <td>TAHUN</td>
                            <td>: {{ $tahun }}</td>
                        </tr>
                        <tr>
                            <td>Tgl Berlaku</td>
                            <td>: 1 JANUARI {{ $tahun }}</td>
                        </tr>
                        <tr>
                            <td>Hal</td>
                            <td>: 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- GRID UTAMA -->
        <table class="table-pm">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">No</th>
                    <th rowspan="2" class="col-nama">Nama Mesin</th>
                    <th rowspan="2" class="col-nomor">NOMOR MESIN</th>
                    <th rowspan="2" class="col-target">Target</th>
                    @for ($m = 1; $m <= 12; $m++)
                        <th colspan="4" style="font-size: 6pt;">
                            {{ strtoupper(Carbon\Carbon::create()->month($m)->isoFormat('MMMM')) }}
                        </th>
                    @endfor
                    <th rowspan="2" style="width: 70px;">Keterangan</th>
                </tr>
                <tr>
                    @for ($m = 1; $m <= 12; $m++)
                        @for ($w = 1; $w <= 4; $w++)
                            <th style="font-size: 4.5pt;">
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
                    $machineData = $jadwalMap[$mId] ?? [];
                @endphp
                <!-- Baris PLAN -->
                <tr>
                    <td rowspan="2" style="font-weight: bold;">{{ $index + 1 }}</td>
                    <td rowspan="2" class="col-nama" style="font-weight: bold;">{{ $mNama }}</td>
                    <td rowspan="2" style="font-weight: bold;">{{ substr($mNama, -2) }}</td>
                    <td class="col-target" style="color: blue;">PLAN</td>
                    @for ($m = 1; $m <= 12; $m++)
                        @for ($w = 1; $w <= 4; $w++)
                        <td>
                            @php $p = $machineData[$m][$w]->plan_tipe ?? ''; @endphp
                            @if(!empty($p))
                                <div class="box-plan">{{ $p }}</div>
                            @endif
                        </td>
                        @endfor
                    @endfor
                    <td rowspan="2" style="font-size: 5pt; text-align: left; vertical-align: top;">
                        @php
                            $latestKet = '';
                            foreach($machineData as $mon) {
                                foreach($mon as $j) {
                                    if(!empty($j->keterangan)) $latestKet = $j->keterangan;
                                }
                            }
                        @endphp
                        {{ $latestKet }}
                    </td>
                </tr>
                <!-- Baris ACTUAL -->
                <tr class="row-actual">
                    <td class="col-target" style="color: green;">ACTUAL</td>
                    @for ($m = 1; $m <= 12; $m++)
                        @for ($w = 1; $w <= 4; $w++)
                        <td>
                            @php $a = $machineData[$m][$w]->aktual_tanggal ?? ''; @endphp
                            @if(!empty($a))
                                <span class="actual-text">{{ $a }}</span>
                            @endif
                        </td>
                        @endfor
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
