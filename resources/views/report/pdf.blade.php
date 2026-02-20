<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Checksheet</title>
    {!! $cssContent ?? '' !!}
</head>

<body>
    <table width="100%" style="border: 2px solid #000; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            {{-- Logo di kiri --}}
            <td style="width: 100px; text-align: center; vertical-align: middle; padding: 15px; border-right: 2px solid #000;">
                @php
                $logoPath = public_path('images/logomwthijau.png');
                if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/png;base64,' . $logoData;
                echo '<img src="'.$logoBase64.'" alt="Logo MWT" style="height: 50px; display: block; margin: 0 auto;">';
                }
                @endphp

            </td>

            {{-- Informasi di kanan --}}
            <td style="text-align: right; vertical-align: middle; padding: 15px;">
                <h1 style="font-size: 18px; margin: 0 0 5px 0; font-weight: bold; text-transform: uppercase; color: #198754;">
                    LAPORAN CHECKSHEET {{ strtoupper($reportData['report_type']) }}
                </h1>
                <p style="margin: 2px 0; font-size: 13px; font-weight: 600;">DIGIPRO - Digital Production System</p>
                <p style="margin: 2px 0; font-size: 12px;">
                    Periode: {{ \Carbon\Carbon::parse($reportData['filters']['start_date'])->format('d/m/Y') }}
                    - {{ \Carbon\Carbon::parse($reportData['filters']['end_date'])->format('d/m/Y') }}
                </p>
                <p style="margin: 2px 0; font-size: 11px; color: #666;">
                    Dibuat pada: {{ $reportData['generated_at']->format('d/m/Y H:i') }} oleh
                    {{ $reportData['generated_by'] }}
                </p>
            </td>
        </tr>
    </table>

    <div class="info-section">
        <h3 style="background-color: #e8f5e9; color: #000; padding: 10px; margin: 0 0 10px 0; font-size: 14px; font-weight: bold; border: 2px solid #000; border-radius: 3px;">Informasi Filter</h3>
        <div class="info-grid" style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <div class="info-row">
                <div class="info-cell info-label">Jenis Laporan</div>
                <div class="info-cell">{{ ucfirst($reportData['report_type']) }}</div>
            </div>
            @if ($reportData['report_type'] === 'inject' && !empty($reportData['filters']['mesin_id']))
            <div class="info-row">
                <div class="info-cell info-label">Mesin</div>
                <div class="info-cell">
                    {{ optional($reportData['data']->first()->mesin)->nama_mesin ?? 'Semua Mesin' }}
                </div>
            </div>
            @endif
            @if ($reportData['report_type'] === 'assy' && !empty($reportData['filters']['meja_id']))
            <div class="info-row">
                <div class="info-cell info-label">Meja</div>
                <div class="info-cell">{{ optional($reportData['data']->first()->meja)->nama_meja ?? 'Semua Meja' }}
                </div>
            </div>
            @endif
            @if (!empty($reportData['filters']['part_id']))
            <div class="info-row">
                <div class="info-cell info-label">Part</div>
                <div class="info-cell">{{ optional($reportData['data']->first()->part)->nama_part ?? 'Semua Part' }}
                </div>
            </div>
            @endif
            @if (!empty($reportData['filters']['user_id']))
            <div class="info-row">
                <div class="info-cell info-label">User</div>
                <div class="info-cell">{{ optional($reportData['data']->first()->user)->nama ?? 'Semua User' }}
                </div>
            </div>
            @endif
            @if (!empty($reportData['filters']['grup_id']))
            <div class="info-row">
                <div class="info-cell info-label">Grup</div>
                <div class="info-cell">
                    {{ optional(optional($reportData['data']->first()->user)->grup)->nama_grup ?? 'Semua Grup' }}
                </div>
            </div>
            @endif
            @if (!empty($reportData['filters']['shift']))
            <div class="info-row">
                <div class="info-cell info-label">Shift</div>
                <div class="info-cell">{{ ucfirst($reportData['filters']['shift']) }}</div>
            </div>
            @endif
            @if (!empty($reportData['filters']['approval_status']))
            <div class="info-row">
                <div class="info-cell info-label">Status Approval</div>
                <div class="info-cell">{{ ucfirst($reportData['filters']['approval_status']) }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="summary-section">
        <h3 style="background-color: #e8f5e9; color: #000; padding: 10px; margin: 0 0 10px 0; font-size: 14px; font-weight: bold; border: 2px solid #000; border-radius: 3px;">Ringkasan Data</h3>
        <div class="summary-grid" style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <div class="summary-item">
                <div class="summary-number">{{ $reportData['summary']['total'] }}</div>
                <div class="summary-label">Total Data</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $reportData['summary']['by_status']['pending'] }}</div>
                <div class="summary-label">Pending</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $reportData['summary']['by_status']['approved'] }}</div>
                <div class="summary-label">Approved</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $reportData['summary']['by_status']['rejected'] }}</div>
                <div class="summary-label">Rejected</div>
            </div>
        </div>
    </div>

    @if ($reportData['data']->count() > 0)
    <div class="data-section">
        <h3 style="background-color: #e8f5e9; color: #000; padding: 10px; margin: 0 0 10px 0; font-size: 14px; font-weight: bold; border: 2px solid #000; border-radius: 3px;">Detail Data Checksheet</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 30px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">No</th>
                    <th style="width: 100px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Tanggal</th>
                    @if ($reportData['report_type'] === 'inject')
                    <th style="width: 80px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Mesin</th>
                    @endif
                    @if ($reportData['report_type'] === 'assy')
                    <th style="width: 80px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Meja</th>
                    @endif
                    <th style="width: 120px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Part</th>
                    <th style="width: 100px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">User</th>
                    <th style="width: 80px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Grup</th>
                    <th style="width: 60px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Shift</th>
                    <th style="width: 70px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Status</th>
                    <th style="width: 100px; background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Diapprove Oleh</th>
                    <th style="background-color: #198754 !important; color: #ffffff !important; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #000; font-size: 10px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportData['data'] as $index => $checksheet)
                <tr>
                    <td style="text-align: center; padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ $index + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ $checksheet->created_at ? $checksheet->created_at->format('d/m/Y H:i') : '-' }}</td>
                    @if ($reportData['report_type'] === 'inject')
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ optional($checksheet->mesin)->nama_mesin ?? '-' }}</td>
                    @endif
                    @if ($reportData['report_type'] === 'assy')
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ optional($checksheet->meja)->nama_meja ?? '-' }}</td>
                    @endif
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ optional($checksheet->part)->nama_part ?? '-' }}</td>
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ optional($checksheet->user)->nama ?? '-' }}</td>
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ optional(optional($checksheet->user)->grup)->nama_grup ?? '-' }}</td>
                    <td style="text-align: center; padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ ucfirst($checksheet->shift ?? '-') }}</td>
                    <td style="text-align: center; padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ ucfirst($checksheet->approval_status ?? '-') }}</td>
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">
                        @if ($checksheet->approval_status === 'approved')
                        {{ optional($checksheet->approver)->nama ?? '-' }}
                        @else
                        -
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #000; font-size: 10px; color: #000;">{{ $checksheet->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="data-section">
        <h3 style="background-color: #e8f5e9; color: #000; padding: 10px; margin: 0 0 10px 0; font-size: 14px; font-weight: bold; border: 2px solid #000; border-radius: 3px;">Detail Data Checksheet</h3>
        <p style="text-align: center; font-style: italic; color: #666;">Tidak ada data yang ditemukan untuk filter
            yang dipilih.</p>
    </div>
    @endif

    @if (isset($reportData['photo_path']) && $reportData['photo_path'])
    <div class="photo-section">
        <h3>Foto Laporan</h3>
        @php
        $photoPath = storage_path('app/public/' . $reportData['photo_path']);
        if (file_exists($photoPath)) {
        $photoData = base64_encode(file_get_contents($photoPath));
        $photoBase64 = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . $photoData;
        echo '<img src="'.$photoBase64.'" alt="Foto Laporan" style="max-width: 300px;">';
        }
        @endphp
        @if (isset($reportData['photo_description']) && $reportData['photo_description'])
        <div class="photo-description">
            {{ $reportData['photo_description'] }}
        </div>
        @endif
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem DIGIPRO</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>

</html>