<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>OEE Dashboard Report</title>
    <style>
        @page {
            margin: 20mm 10mm 15mm 10mm;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.2;
            background: #fff;
            padding: 0 15mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Header Styles */
        .report-header {
            margin-top: 10px;
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 15px;
        }

        .report-header table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .report-header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .company-logo {
            max-height: 50px;
            width: auto;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 0;
        }

        .report-meta {
            text-align: right;
            font-size: 8pt;
            line-height: 1.3;
        }

        .report-meta strong {
            color: #000;
        }

        /* KPI Section */
        .kpi-section {
            margin: 4px 0;
        }

        .kpi-section table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: fixed;
        }

        .kpi-section td {
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
            background: #f5f5f5;
            width: 25%;
        }

        .kpi-label {
            font-size: 7pt;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            display: block;
        }

        /* Charts Section */
        .charts-section {
            margin: 4px 0;
        }

        .chart-container {
            width: 100%;
            margin-bottom: 4px;
            page-break-inside: avoid;
            text-align: center;
        }

        .chart-container table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .chart-container td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }

        .chart-img {
            max-width: 100%;
            max-height: 280px;
            width: auto;
            height: auto;
            display: inline-block;
        }

        /* Data Section Wrapper */
        .data-section {
            margin-top: 5px;
        }

        /* Summary Box */
        .summary-box {
            background: #e3f2fd;
            border-left: 3px solid #1976d2;
            padding: 5px 8px;
            margin-bottom: 5px;
            font-size: 8pt;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table thead tr {
            background: #d0d0d0;
            border: 1px solid #000;
        }

        .data-table th {
            border: 1px solid #000;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            font-size: 7pt;
            line-height: 1.1;
        }

        .data-table tbody tr {
            border: 1px solid #999;
            page-break-inside: avoid;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8f8f8;
        }

        .data-table tbody tr:nth-child(odd) {
            background: #fff;
        }

        .data-table td {
            border: 1px solid #999;
            padding: 6px 6px;
            vertical-align: middle;
        }

        .col-date {
            text-align: center;
            white-space: nowrap;
            width: 9%;
        }

        .col-machine {
            text-align: left;
            font-weight: 600;
            width: 15%;
        }

        .col-part {
            text-align: left;
            width: 13%;
        }

        .col-num {
            text-align: right;
            padding-right: 4px;
            white-space: nowrap;
            width: 7%;
        }

        .col-percent {
            text-align: right;
            padding-right: 4px;
            white-space: nowrap;
            width: 9%;
        }

        .col-oee {
            text-align: right;
            padding-right: 4px;
            font-weight: bold;
            color: #1565c0;
            width: 8%;
        }

        .text-good {
            color: #2e7d32;
            font-weight: 600;
        }

        .text-ng {
            color: #c62828;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 10px;
            font-size: 9pt;
            color: #666;
        }

        /* Footer */
        .report-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #555;
            border-top: 1px solid #999;
            padding-top: 2px;
            background: #fff;
        }

        /* Print Optimization */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .data-table thead {
                display: table-header-group;
            }

            .data-table tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
    $logoPath = public_path('images/logomwthijau.png');
    $logoBase64 = null;
    if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    @endphp

    <!-- Header -->
    <div class="report-header">
        <table>
            <tr>
                <td style="width: 20%; vertical-align: middle; text-align: left; border-right: 1px solid #000; padding-right: 10px;">
                    @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Company Logo" class="company-logo">
                    @else
                    <div style="font-weight:bold; color:red;">[LOGO]</div>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: middle; text-align: center; border-right: 1px solid #000; padding: 0 10px;">
                    <div class="company-name">PT MADA WIKRI TUNGGAL</div>
                    <div class="report-title">OEE DASHBOARD REPORT</div>
                </td>
                <td style="width: 35%; vertical-align: middle; text-align: right; padding-left: 10px;">
                    <div class="report-meta">
                        <div><strong>Generated:</strong> {{ date('d-m-Y H:i:s') }}</div>
                        <div><strong>Periode:</strong> {{ strtoupper($period ?? 'MONTH') }}</div>
                        <div><strong>Rentang:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-section">
        <table>
            <tr>
                <td>
                    <span class="kpi-label">Overall OEE</span>
                    <span class="kpi-value">{{ number_format($avgOEE ?? 0, 1) }}%</span>
                </td>
                <td>
                    <span class="kpi-label">Availability</span>
                    <span class="kpi-value">{{ number_format($avgAvailability ?? 0, 1) }}%</span>
                </td>
                <td>
                    <span class="kpi-label">Performance</span>
                    <span class="kpi-value">{{ number_format($avgPerformance ?? 0, 1) }}%</span>
                </td>
                <td>
                    <span class="kpi-label">Quality</span>
                    <span class="kpi-value">{{ number_format($avgQuality ?? 0, 1) }}%</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Charts -->
    <div class="charts-section">
        <!-- Row 1: OEE Trend (Full Width) -->
        @if(!empty($trendChartUrl))
        <div class="chart-container">
            <img src="{{ $trendChartUrl }}" alt="OEE Trend" class="chart-img" style="width: 100%; max-height: 280px;">
        </div>
        @endif

        <!-- Row 2: Machine Performance & NG Analysis (Side by Side) -->
        @if(!empty($machineChartUrl) || !empty($ngChartUrl))
        <div class="chart-container" style="margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    @if(!empty($machineChartUrl))
                    <td style="width: 60%; vertical-align: top; padding-right: 5px;">
                        <img src="{{ $machineChartUrl }}" alt="Machine Performance" class="chart-img" style="width: 100%; max-height: 280px;">
                    </td>
                    @endif

                    @if(!empty($ngChartUrl))
                    <td style="width: 40%; vertical-align: top; padding-left: 5px;">
                        <img src="{{ $ngChartUrl }}" alt="NG Category" class="chart-img" style="width: 100%; max-height: 280px;">
                    </td>
                    @endif
                </tr>
            </table>
        </div>
        @endif
    </div>

    <!-- NG Breakdown Table (PDF) -->
    @if(isset($chartData['ngLabels']) && isset($chartData['ngCategories']))
    <div style="margin-bottom: 10px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
            <thead>
                <tr style="background: #f0f0f0; border: 1px solid #999;">
                    <th colspan="6" style="padding: 3px; text-align: left; border: 1px solid #999;">DETAIL BREAKDOWN NG (TOP 12)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @php
                    $ngItems = [];
                    foreach($chartData['ngLabels'] as $idx => $label) {
                    if($chartData['ngCategories'][$idx] > 0) {
                    $ngItems[] = ['label' => $label, 'count' => $chartData['ngCategories'][$idx]];
                    }
                    }
                    // Sort by count descending
                    usort($ngItems, function($a, $b) { return $b['count'] <=> $a['count']; });
                        $ngItems = array_slice((array)$ngItems, 0, 12); // Show top 12
                        @endphp

                        @if(count($ngItems) > 0)
                        @foreach(array_chunk($ngItems, 4) as $chunk)
                        <td style="width: 33.33%; vertical-align: top; padding: 0;">
                            <table style="width: 100%; border-collapse: collapse;">
                                @foreach($chunk as $item)
                                <tr>
                                    <td style="border: 1px solid #999; padding: 2px 4px; width: 70%;">{{ $item['label'] }}</td>
                                    <td style="border: 1px solid #999; padding: 2px 4px; text-align: right; font-weight: bold;">{{ number_format($item['count']) }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                        @endforeach
                        @else
                        <td colspan="3" style="border: 1px solid #999; padding: 5px; text-align: center; color: #666;">Tidak ada data NG untuk periode ini</td>
                        @endif
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Data Section (Summary + Table) -->
    <div class="data-section">
        <!-- Summary -->
        @if(!empty($oeeData) && (is_array($oeeData) || $oeeData instanceof \Countable) && count($oeeData) > 0)
        <div class="summary-box">
            <strong>Summary:</strong> Menampilkan {{ (is_array($oeeData) || $oeeData instanceof \Countable) ? count($oeeData) : 0 }} data OEE yang sudah di-approve untuk periode yang dipilih.
        </div>
        @endif

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-date">Tanggal</th>
                    <th class="col-machine">Mesin</th>
                    <th class="col-part">Part Number</th>
                    <th class="col-num">Output</th>
                    <th class="col-num">Good</th>
                    <th class="col-num">NG</th>
                    <th class="col-percent">Availability</th>
                    <th class="col-percent">Performance</th>
                    <th class="col-percent">Quality</th>
                    <th class="col-oee">OEE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($oeeData ?? [] as $oee)
                <tr>
                    <td class="col-date">
                        @if(isset($oee->tanggal) && $oee->tanggal)
                        {{ $oee->tanggal instanceof \Carbon\Carbon ? $oee->tanggal->format('d-m-Y') : $oee->tanggal }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="col-machine">{{ $oee->mesin->nama_mesin ?? ($oee->planning->mesin->nama_mesin ?? 'N/A') }}</td>
                    <td class="col-part">{{ $oee->planning->part->no_parts ?? 'N/A' }}</td>
                    <td class="col-num">{{ number_format($oee->actual_output ?? 0, 0) }}</td>
                    <td class="col-num text-good">{{ number_format($oee->good_output ?? 0, 0) }}</td>
                    <td class="col-num text-ng">{{ number_format($oee->ng_output ?? 0, 0) }}</td>
                    <td class="col-percent">{{ number_format($oee->availability_rate ?? 0, 1) }}%</td>
                    <td class="col-percent">{{ number_format($oee->performance_rate ?? 0, 1) }}%</td>
                    <td class="col-percent">{{ number_format($oee->quality_rate ?? 0, 1) }}%</td>
                    <td class="col-oee">{{ number_format($oee->oee_rate ?? 0, 1) }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <strong>Tidak ada data OEE yang tersedia</strong><br>
                        <small>Pastikan ada data produksi yang sudah di-approve untuk periode yang dipilih</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="report-footer">
        <strong>DIGIPRO - Digital Production System</strong> | OEE Dashboard Report | &copy; {{ date('Y') }} PT Mada Wikri Tunggal
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7;
            $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $y = $pdf->get_height() - 20;
            $x = $pdf->get_width() - 80;
            $pdf->page_text($x, $y, $pageText, $font, $size, [0.3, 0.3, 0.3]);
        }
    </script>
</body>

</html>