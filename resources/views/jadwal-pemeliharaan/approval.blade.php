@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h1 class="h3 mb-0 text-white-shadow"><i class="fas fa-check-circle text-success"></i> Approval Preventive</h1>
        <a href="{{ route('jadwal-pemeliharaan.index') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-calendar-alt me-2"></i> Kembali ke Jadwal
        </a>
    </div>

    <div class="card shadow mb-4 bg-dark-card border-0">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-list me-2"></i> Daftar Tunggu Persetujuan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-bordered mb-0" id="approvalTable">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Mesin</th>
                            <th>Periode</th>
                            <th>Tgl Realisasi</th>
                            <th>Bukti</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingJadwals as $index => $jadwal)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $jadwal->mesin->nama_mesin }}</div>
                                <small class="text-muted">ID: {{ $jadwal->mesin->id }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">
                                    Thn {{ $jadwal->tahun }} | Bln {{ $jadwal->bulan }} | Mg {{ $jadwal->minggu }}
                                </span>
                            </td>
                            <td class="text-center fw-bold text-success">{{ $jadwal->aktual_tanggal }}</td>
                            <td class="text-center">
                                @if($jadwal->bukti)
                                    <a href="{{ asset('storage/maintenance_bukti/' . $jadwal->bukti) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye me-1"></i> Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-muted small">Tidak ada bukti</span>
                                @endif
                            </td>
                            <td>{{ $jadwal->keterangan ?: '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-primary btn-sm btn-detail" 
                                        data-id="{{ $jadwal->id }}" 
                                        data-mesin="{{ $jadwal->mesin->nama_mesin }}"
                                        data-periode="Thn {{ $jadwal->tahun }} | Bln {{ $jadwal->bulan }} | Mg {{ $jadwal->minggu }}"
                                        data-tipe="{{ $jadwal->plan_tipe ?: 'Regular' }}"
                                        data-tanggal="{{ $jadwal->aktual_tanggal }}"
                                        data-keterangan="{{ $jadwal->keterangan ?: '-' }}"
                                        data-bukti="{{ $jadwal->bukti ? asset('storage/maintenance_bukti/' . $jadwal->bukti) : '' }}"
                                        title="Detail">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                    <button class="btn btn-success btn-sm btn-approve" data-id="{{ $jadwal->id }}" title="Approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-reject" data-id="{{ $jadwal->id }}" title="Reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                                    <p>Tidak ada pengajuan realisasi yang menunggu persetujuan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark-card border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="detailModalLabel"><i class="fas fa-info-circle"></i> Detail Realisasi Preventive</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Nama Mesin</label>
                        <div id="modal-mesin" class="fw-bold"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Periode</label>
                        <div id="modal-periode"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Tipe Planning</label>
                        <div id="modal-tipe"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small d-block">Tanggal Aktual</label>
                        <div id="modal-tanggal" class="text-success fw-bold"></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="text-muted small d-block">Keterangan</label>
                        <div id="modal-keterangan" class="border p-2 rounded bg-dark" style="min-height: 60px;"></div>
                    </div>
                    <div class="col-12 mb-3 mt-2">
                        <label class="text-muted small d-block mb-2">Bukti Realisasi</label>
                        <div id="modal-bukti-container" class="text-center p-3 border rounded bg-dark">
                            <img id="modal-bukti-img" src="" class="img-fluid rounded mb-2 shadow d-none" style="max-height: 400px;">
                            <div id="modal-bukti-none" class="text-muted d-none">Tidak ada bukti gambar</div>
                            <div class="mt-2">
                                <a id="modal-bukti-link" href="#" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-external-link-alt"></i> Buka File Asli
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable for better UX
    if ($.fn.DataTable) {
        $('#approvalTable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "language": {
                "emptyTable": "Tidak ada pengajuan realisasi yang menunggu persetujuan.",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "lengthMenu": "Tampilkan _MENU_ data",
                "search": "Cari:",
                "zeroRecords": "Data tidak ditemukan",
                "paginate": {
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    }

    // Detail Modal Trigger
    $(document).on('click', '.btn-detail', function() {
        const data = $(this).data();
        $('#modal-mesin').text(data.mesin);
        $('#modal-periode').text(data.periode);
        $('#modal-tipe').text(data.tipe);
        $('#modal-tanggal').text(data.tanggal);
        $('#modal-keterangan').text(data.keterangan);
        
        if (data.bukti) {
            $('#modal-bukti-img').attr('src', data.bukti).removeClass('d-none');
            $('#modal-bukti-link').attr('href', data.bukti).removeClass('d-none');
            $('#modal-bukti-none').addClass('d-none');
        } else {
            $('#modal-bukti-img').addClass('d-none');
            $('#modal-bukti-link').addClass('d-none');
            $('#modal-bukti-none').removeClass('d-none');
        }
        
        $('#detailModal').modal('show');
    });

    // Approve Action - Use Event Delegation
    $(document).on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Realisasi maintenance akan disetujui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#343a40',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyetujui realisasi...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ url('jadwal-pemeliharaan/approve') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil!', response.message, 'success');
                            // If DataTable is initialized, row removal is different
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvalTable')) {
                                let table = $('#approvalTable').DataTable();
                                table.row(row).remove().draw(false);
                            } else {
                                row.fadeOut(500, function() { $(this).remove(); });
                            }
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });

    // Reject Action - Use Event Delegation
    $(document).on('click', '.btn-reject', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Tolak Realisasi?',
            text: "Silakan berikan alasan penolakan (opsional):",
            input: 'text',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#343a40',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menolak realisasi...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ url('jadwal-pemeliharaan/reject') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        alasan: result.value
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Ditolak!', response.message, 'success');
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvalTable')) {
                                let table = $('#approvalTable').DataTable();
                                table.row(row).remove().draw(false);
                            } else {
                                row.fadeOut(500, function() { $(this).remove(); });
                            }
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

@push('css')
<style>
    .bg-dark-card {
        background-color: #1e1e1e !important;
        color: #fff;
    }
    .text-white-shadow {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        color: #fff;
    }
    .table-dark {
        background-color: #252525;
    }
    .badge-info {
        background-color: #0d6efd;
    }
</style>
@endpush
