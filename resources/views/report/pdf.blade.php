<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Checksheet</title>
    <link rel="stylesheet" href="{{ public_path('css/report-pdf.css') }}">
    <link rel="stylesheet" href="{{ public_path('css/report-pdf-template.css') }}">
</head>
<body>
    <div class="header">
        <h1>LAPORAN CHECKSHEET {{ strtoupper($reportData['report_type']) }}</h1>
        <p>DIGIPRO - Digital Production System</p>
        <p>Periode: {{ \Carbon\Carbon::parse($reportData['filters']['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reportData['filters']['end_date'])->format('d/m/Y') }}</p>
        <p>Dibuat pada: {{ $reportData['generated_at']->format('d/m/Y H:i') }} oleh {{ $reportData['generated_by'] }}</p>
    </div>

    <div class="info-section">
        <h3>Informasi Filter</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Jenis Laporan</div>
                <div class="info-cell">{{ ucfirst($reportData['report_type']) }}</div>
            </div>
            @if($reportData['report_type'] === 'inject')
                @if(!empty($reportData['filters']['mesin_id']))
                <div class="info-row">
                    <div class="info-cell info-label">Mesin</div>
                    <div class="info-cell">{{ $reportData['data']->first()->mesin->nama_mesin ?? 'Semua Mesin' }}</div>
                </div>
                @endif
            @endif
            @if($reportData['report_type'] === 'assy')
                @if(!empty($reportData['filters']['meja_id']))
                <div class="info-row">
                    <div class="info-cell info-label">Meja</div>
                    <div class="info-cell">{{ $reportData['data']->first()->meja->nama_meja ?? 'Semua Meja' }}</div>
                </div>
                @endif
            @endif
            @if(!empty($reportData['filters']['part_id']))
            <div class="info-row">
                <div class="info-cell info-label">Part</div>
                <div class="info-cell">{{ $reportData['data']->first()->part->nama_part ?? 'Semua Part' }}</div>
            </div>
            @endif
            @if(!empty($reportData['filters']['user_id']))
            <div class="info-row">
                <div class="info-cell info-label">User</div>
                <div class="info-cell">{{ $reportData['data']->first()->user->nama ?? 'Semua User' }}</div>
            </div>
            @endif
            @if(!empty($reportData['filters']['grup_id']))
            <div class="info-row">
                <div class="info-cell info-label">Grup</div>
                <div class="info-cell">{{ $reportData['data']->first()->user->grup->nama_grup ?? 'Semua Grup' }}</div>
            </div>
            @endif
            @if(!empty($reportData['filters']['shift']))
            <div class="info-row">
                <div class="info-cell info-label">Shift</div>
                <div class="info-cell">{{ ucfirst($reportData['filters']['shift']) }}</div>
            </div>
            @endif
            @if(!empty($reportData['filters']['approval_status']))
            <div class="info-row">
                <div class="info-cell info-label">Status Approval</div>
                <div class="info-cell">{{ ucfirst($reportData['filters']['approval_status']) }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="summary-section">
        <h3>Ringkasan Data</h3>
        <div class="summary-grid">
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

    @if($reportData['data']->count() > 0)
    <div class="data-section">
        <h3>Detail Data Checksheet</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    @if($reportData['report_type'] === 'inject')
                        <th>Mesin</th>
                    @endif
                    @if($reportData['report_type'] === 'assy')
                        <th>Meja</th>
                    @endif
                    <th>Part</th>
                    <th>User</th>
                    <th>Grup</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th>Diapprove Oleh</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['data'] as $index => $checksheet)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $checksheet->created_at ? $checksheet->created_at->format('d/m/Y H:i') : '-' }}</td>
                    @if($reportData['report_type'] === 'inject')
                        <td>{{ $checksheet->mesin ? $checksheet->mesin->nama_mesin : '-' }}</td>
                    @endif
                    @if($reportData['report_type'] === 'assy')
                        <td>{{ $checksheet->meja ? $checksheet->meja->nama_meja : '-' }}</td>
                    @endif
                    <td>{{ $checksheet->part ? $checksheet->part->nama_part : '-' }}</td>
                    <td>{{ $checksheet->user ? $checksheet->user->nama : '-' }}</td>
                    <td>{{ $checksheet->user ? $checksheet->user->grup->nama_grup : '-' }}</td>
                    <td style="text-align: center;">{{ ucfirst($checksheet->shift ?? '-') }}</td>
                    <td style="text-align: center;">{{ ucfirst($checksheet->approval_status ?? '-') }}</td>
                    <td>
                        @if($checksheet->approval_status === 'approved')
                            {{ $checksheet->approver ? $checksheet->approver->nama : '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $checksheet->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="data-section">
        <h3>Detail Data Checksheet</h3>
        <p style="text-align: center; font-style: italic; color: #666;">Tidak ada data yang ditemukan untuk filter yang dipilih.</p>
    </div>
    @endif

    @if($reportData['photo_path'])
    <div class="photo-section">
        <h3>Foto Laporan</h3>
        <img src="{{ storage_path('app/public/' . $reportData['photo_path']) }}" alt="Foto Laporan">
        @if($reportData['photo_description'])
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