<!-- General checksheets for other roles -->
<div class="card recent-checksheets">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-2"></i>
            Checksheet Terbaru
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createReportModal">
                <i class="fas fa-plus mr-1"></i>Buat Laporan
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($recentChecksheets->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Mesin/Meja</th>
                            <th>Part</th>
                            <th>User</th>
                            <th>Grup</th>
                            <th>Shift</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentChecksheets as $index => $checksheet)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $checksheet->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($checksheet->mesin)
                                    {{ $checksheet->mesin->nama_mesin }}
                                @elseif($checksheet->meja)
                                    {{ $checksheet->meja->nama_meja }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $checksheet->part->nama_part ?? '-' }}</td>
                            <td>{{ $checksheet->user->nama ?? '-' }}</td>
                            <td>{{ $checksheet->user->grup->nama_grup ?? '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($checksheet->shift) }}</span>
                            </td>
                            <td>
                                @if($checksheet->approval_status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($checksheet->approval_status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-check"></i>
                <h5>Belum ada data checksheet</h5>
                <p>Mulai dengan membuat laporan baru</p>
            </div>
        @endif
    </div>
</div> 