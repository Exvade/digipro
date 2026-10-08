@extends('layouts.app')

@section('title', 'Approval Realisasi Preventive - DIGIPRO System')

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/25 flex-shrink-0">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Approval Realisasi Preventive
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>{{ count($pendingJadwals) }} Menunggu Persetujuan</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Verifikasi dan otorisasi bukti pekerjaan pemeliharaan mesin dari teknisi lapangan
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('jadwal-pemeliharaan.index') }}" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-750 shadow-sm transition-all cursor-pointer">
            <i class="fas fa-calendar-alt text-xs text-indigo-500"></i>
            <span>Kembali ke Jadwal</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Workflow Info Banner --}}
    <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-indigo-500/10 border border-emerald-200/60 dark:border-emerald-800/40 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-md shadow-emerald-500/25 mt-0.5">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>

    {{-- Main Approval Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0 flex items-center gap-2">
                    <i class="fas fa-list text-emerald-500"></i>
                    <span>Daftar Tunggu Persetujuan Realisasi</span>
                </h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                    Klik tombol aksi untuk melihat rincian foto, menyetujui, atau menolak pengajuan
                </p>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="approvalTable">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/60">
                            <th class="py-3 px-4 text-center rounded-l-xl" style="width: 50px;">No</th>
                            <th class="py-3 px-4">Mesin</th>
                            <th class="py-3 px-4 text-center">Periode</th>
                            <th class="py-3 px-4 text-center">Tgl Realisasi</th>
                            <th class="py-3 px-4 text-center">Bukti</th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4 text-center rounded-r-xl" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                        @foreach($pendingJadwals as $index => $jadwal)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500 dark:text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $jadwal->mesin->nama_mesin }}
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1.5">
                                    <span>ID: {{ $jadwal->mesin->id }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $jadwal->mesin->bagian ?? 'Mesin' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                                    <i class="far fa-calendar-alt text-[10px]"></i>
                                    <span>Thn {{ $jadwal->tahun }} | Bln {{ $jadwal->bulan }} | Mg {{ $jadwal->minggu }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                    <i class="fas fa-check text-[10px]"></i>
                                    <span>{{ $jadwal->aktual_tanggal }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($jadwal->bukti)
                                    <a href="{{ asset('storage/maintenance_bukti/' . $jadwal->bukti) }}" target="_blank" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/80 hover:bg-sky-100 dark:hover:bg-sky-900/60 transition-all">
                                        <i class="fas fa-image text-sky-500"></i>
                                        <span>Lihat Bukti</span>
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Tidak ada bukti</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="max-w-xs truncate text-slate-600 dark:text-slate-300 font-medium" title="{{ $jadwal->keterangan }}">
                                    {{ $jadwal->keterangan ?: '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                        class="btn-detail w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-400 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/60 flex items-center justify-center text-xs transition-all cursor-pointer" 
                                        data-id="{{ $jadwal->id }}" 
                                        data-mesin="{{ $jadwal->mesin->nama_mesin }}"
                                        data-periode="Tahun {{ $jadwal->tahun }} | Bulan {{ $jadwal->bulan }} | Minggu ke-{{ $jadwal->minggu }}"
                                        data-tipe="{{ $jadwal->plan_tipe ?: 'Regular' }}"
                                        data-tanggal="{{ $jadwal->aktual_tanggal }}"
                                        data-keterangan="{{ $jadwal->keterangan ?: '-' }}"
                                        data-bukti="{{ $jadwal->bukti ? asset('storage/maintenance_bukti/' . $jadwal->bukti) : '' }}"
                                        title="Lihat Detail Realisasi">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <button type="button" 
                                        class="btn-approve w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/60 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center text-xs transition-all cursor-pointer" 
                                        data-id="{{ $jadwal->id }}" 
                                        title="Setujui (Approve)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" 
                                        class="btn-reject w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-950/60 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white border border-rose-200/80 dark:border-rose-800/60 flex items-center justify-center text-xs transition-all cursor-pointer" 
                                        data-id="{{ $jadwal->id }}" 
                                        title="Tolak (Reject)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Realisasi (Tailwind CSS) --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold tracking-tight text-white mb-0 leading-tight" id="detailModalLabel">
                            Detail Realisasi Preventive Maintenance
                        </h5>
                        <p class="text-[11px] text-teal-100 font-medium mb-0">
                            Informasi pengerjaan dan bukti verifikasi lapangan
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer border-0" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nama Mesin --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Nama Mesin</span>
                        <div id="modal-mesin" class="text-sm font-bold text-slate-900 dark:text-white"></div>
                    </div>

                    {{-- Periode --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Periode Jadwal</span>
                        <div id="modal-periode" class="text-sm font-semibold text-slate-700 dark:text-slate-300"></div>
                    </div>

                    {{-- Tipe Planning --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Tipe Planning</span>
                        <div id="modal-tipe" class="text-sm font-bold text-indigo-600 dark:text-indigo-400"></div>
                    </div>

                    {{-- Tanggal Aktual --}}
                    <div class="p-3.5 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-1">Tanggal Realisasi (Aktual)</span>
                        <div id="modal-tanggal" class="text-sm font-black text-emerald-600 dark:text-emerald-400"></div>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Catatan / Keterangan</span>
                    <div id="modal-keterangan" class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line min-h-[40px]"></div>
                </div>

                {{-- Bukti Realisasi --}}
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Bukti Foto / Dokumen Lapangan</span>
                    <div id="modal-bukti-container" class="text-center">
                        <img id="modal-bukti-img" src="" class="max-h-80 mx-auto rounded-xl shadow-md border border-slate-200 dark:border-slate-700 object-contain hidden">
                        <div id="modal-bukti-none" class="py-6 text-xs text-slate-400 italic hidden">
                            <i class="fas fa-image fa-2x mb-2 text-slate-300 dark:text-slate-600 block"></i>
                            Tidak ada file bukti yang diunggah
                        </div>
                        <div class="pt-3">
                            <a id="modal-bukti-link" href="#" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-100 transition-all hidden">
                                <i class="fas fa-external-link-alt"></i>
                                <span>Buka File Dokumen Asli</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200/90 dark:border-slate-800 flex items-center justify-end">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer border-0" data-dismiss="modal" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable Approval dengan gaya modern Tailwind
    if ($.fn.DataTable) {
        $('#approvalTable').DataTable({
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>' +
                 '<"overflow-x-auto"t>' +
                 '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4"<"text-xs text-slate-500 dark:text-slate-400"i><"flex items-center justify-end"p>>',
            language: {
                emptyTable: "Tidak ada pengajuan realisasi yang menunggu persetujuan.",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                lengthMenu: "Tampilkan _MENU_ data",
                loadingRecords: "Memuat data...",
                processing: "Sedang memproses...",
                search: "",
                searchPlaceholder: "Cari mesin atau periode...",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: '<i class="fas fa-angle-double-left text-xs"></i>',
                    last: '<i class="fas fa-angle-double-right text-xs"></i>',
                    next: '<i class="fas fa-chevron-right text-xs"></i>',
                    previous: '<i class="fas fa-chevron-left text-xs"></i>'
                }
            }
        });

        // Styling Tailwind untuk input search & select box DataTable
        $('#approvalTable_filter input').addClass('px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400');
        $('#approvalTable_length select').addClass('px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all');
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
            $('#modal-bukti-img').attr('src', data.bukti).removeClass('hidden');
            $('#modal-bukti-link').attr('href', data.bukti).removeClass('hidden');
            $('#modal-bukti-none').addClass('hidden');
        } else {
            $('#modal-bukti-img').addClass('hidden');
            $('#modal-bukti-link').addClass('hidden');
            $('#modal-bukti-none').removeClass('hidden');
        }
        
        $('#detailModal').modal('show');
    });

    // Approve Action - Event Delegation
    $(document).on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Setujui Realisasi?',
            text: "Realisasi pemeliharaan mesin ini akan disetujui dan statusnya menjadi APPROVED.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Setujui',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan persetujuan...',
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvalTable')) {
                                let table = $('#approvalTable').DataTable();
                                table.row(row).remove().draw(false);
                            } else {
                                row.fadeOut(400, function() { $(this).remove(); });
                            }
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });

    // Reject Action - Event Delegation
    $(document).on('click', '.btn-reject', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Tolak Realisasi?',
            text: "Berikan alasan penolakan agar tim teknisi dapat memperbaikinya:",
            input: 'text',
            inputPlaceholder: 'Tuliskan catatan perbaikan / alasan...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Tolak',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Pengajuan Ditolak',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvalTable')) {
                                let table = $('#approvalTable').DataTable();
                                table.row(row).remove().draw(false);
                            } else {
                                row.fadeOut(400, function() { $(this).remove(); });
                            }
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
