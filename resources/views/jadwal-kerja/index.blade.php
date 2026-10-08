@extends('jadwal-kerja.app')

@section('title', 'Jadwal Kerja - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Custom Jadwal Kerja CSS -->
<link rel="stylesheet" href="{{ asset('css/jadwal-kerja.css') }}?v={{ filemtime(public_path('css/jadwal-kerja.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-500/20 flex-shrink-0">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                Jadwal Kerja
            </h1>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 mb-0">
                Manajemen penugasan shift dan jadwal kerja karyawan produksi
            </p>
        </div>
    </div>

    @if (in_array(auth()->user()->role, ['super_admin', 'staff', 'manager_plant', 'kabag']))
    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer" data-toggle="modal" data-target="#importExcelModal">
            <i class="fas fa-file-excel text-[11px]"></i>
            <span>Import Excel</span>
        </button>
        <button type="button" id="tambahJadwalBtn" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus text-[11px]"></i>
            <span>Tambah Jadwal</span>
        </button>
    </div>
    @endif
</div>
@endsection

@section('content')
@php
$restrictedRoles = ['leader', 'foreman', 'operator'];
@endphp

<div class="space-y-4">
    {{-- Filter Data (Hanya untuk non-restricted roles) --}}
    @if (!in_array(auth()->user()->role, $restrictedRoles))
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm transition-all duration-200">
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                    <i class="fas fa-filter text-[10px]"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider mb-0">
                    Filter Jadwal Kerja
                </h3>
            </div>
            @if(request()->hasAny(['search', 'tanggal', 'shift', 'bagian']))
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300/40">
                Filter Aktif
            </span>
            @endif
        </div>

        <form method="GET" action="{{ route('jadwal-kerja.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <!-- Cari Karyawan -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                    Cari Karyawan
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK..." class="w-full pl-8 pr-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[11px]"></i>
                </div>
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                    Tanggal
                </label>
                <div class="relative">
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                </div>
            </div>

            <!-- Shift -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                    Shift
                </label>
                <select name="shift" class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    <option value="">Semua Shift</option>
                    @foreach (['pagi', 'siang', 'malam', 'off'] as $s)
                    <option value="{{ $s }}" {{ request('shift') == $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Bagian -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                    Bagian
                </label>
                <select name="bagian" class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    <option value="">Semua Bagian</option>
                    @foreach (['inject', 'assy'] as $b)
                    <option value="{{ $b }}" {{ request('bagian') == $b ? 'selected' : '' }}>
                        {{ ucfirst($b) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm shadow-indigo-500/20 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <i class="fas fa-search text-[10px]"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('jadwal-kerja.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition-all duration-200 cursor-pointer" title="Reset Filter">
                    <i class="fas fa-redo text-[10px]"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>
    @endif

    {{-- Main Table Section --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden" data-jadwal-kerja-show-url="{{ route('jadwal-kerja.show', ':id') }}">
        <!-- Table Header Bar -->
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2.5">
                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">
                    Daftar Jadwal Kerja Karyawan
                </h2>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/80">
                    {{ $jadwalKerjas->total() }} Total
                </span>
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                Halaman {{ $jadwalKerjas->currentPage() }} dari {{ $jadwalKerjas->lastPage() }}
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table id="jadwalKerjaTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3.5 text-center" style="width: 50px;">No</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-3.5 text-center">Shift</th>
                        <th class="py-3 px-4">Jam Kerja</th>
                        <th class="py-3 px-3.5 text-center">Bagian</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-4 text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @php
                    $startIndex = isset($jadwalKerjas) && method_exists($jadwalKerjas, 'firstItem')
                        ? ($jadwalKerjas->firstItem() ?? 1)
                        : 1;
                    @endphp

                    @forelse ($jadwalKerjas as $index => $jadwal)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- No -->
                        <td class="py-3 px-3.5 text-center font-bold text-slate-400 dark:text-slate-500 text-xs">
                            {{ $startIndex + $index }}
                        </td>

                        <!-- Karyawan -->
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800 dark:text-slate-100 truncate">
                                {{ $jadwal->user->nama ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 capitalize">
                                {{ ucfirst($jadwal->user->role ?? '-') }}
                            </div>
                        </td>

                        <!-- Tanggal -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd') }}
                            </div>
                        </td>

                        <!-- Shift -->
                        <td class="py-3 px-3.5 text-center whitespace-nowrap">
                            @php
                            $shiftBadges = [
                                'pagi' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300/40 dark:border-emerald-700/40',
                                'siang' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300/40 dark:border-amber-700/40',
                                'malam' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border-indigo-300/40 dark:border-indigo-700/40',
                                'off' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border-slate-300/40 dark:border-slate-700/40',
                            ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black border uppercase tracking-wider {{ $shiftBadges[$jadwal->shift] ?? $shiftBadges['off'] }}">
                                {{ ucfirst($jadwal->shift) }}
                            </span>
                        </td>

                        <!-- Jam Kerja -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if ($jadwal->shift !== 'off')
                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                {{ $jadwal->formatted_jam_masuk }} - {{ $jadwal->formatted_jam_keluar }}
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                {{ $jadwal->durasi_kerja }} jam kerja
                            </div>
                            @else
                            <span class="text-slate-400 dark:text-slate-500 font-medium italic">Libur / Off</span>
                            @endif
                        </td>

                        <!-- Bagian -->
                        <td class="py-3 px-3.5 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ $jadwal->user ? ucfirst($jadwal->user->bagian ?? '-') : '-' }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3.5 text-center whitespace-nowrap">
                            @if (($jadwal->status ?? '') === 'aktif')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Tidak Aktif
                            </span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <!-- Detail -->
                                <button type="button" class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 flex items-center justify-center hover:bg-sky-600 hover:text-white dark:hover:bg-sky-500 transition-all btn-detail cursor-pointer" data-id="{{ $jadwal->id }}" title="Detail Jadwal">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </button>

                                @if (in_array(auth()->user()->role, ['staff', 'super_admin']))
                                <!-- Edit -->
                                <button type="button" class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center hover:bg-amber-600 hover:text-white dark:hover:bg-amber-500 transition-all btn-edit cursor-pointer" data-id="{{ $jadwal->id }}" title="Edit Jadwal">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </button>

                                <!-- Hapus -->
                                <button type="button" class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center justify-center hover:bg-rose-600 hover:text-white dark:hover:bg-rose-500 transition-all btn-delete cursor-pointer" data-id="{{ $jadwal->id }}" data-nama="{{ $jadwal->user->nama ?? '-' }}" title="Hapus Jadwal">
                                    <i class="fas fa-trash text-[11px]"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3 text-xl">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum Ada Jadwal Kerja</div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Silakan tambahkan jadwal baru atau import dari file Excel.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination -->
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                Menampilkan {{ $jadwalKerjas->firstItem() ?? 0 }} - {{ $jadwalKerjas->lastItem() ?? 0 }} dari {{ $jadwalKerjas->total() }} total jadwal
            </div>
            @if($jadwalKerjas->hasPages())
            <div>
                {{ $jadwalKerjas->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
            <div class="px-6 py-4 bg-gradient-to-r from-sky-500/10 via-sky-500/5 to-transparent dark:from-sky-500/20 border-b border-sky-200/50 dark:border-sky-900/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="detailModalLabel">
                            Rincian Jadwal Kerja
                        </h5>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Informasi detail karyawan dan penugasan shift</p>
                    </div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="p-6" id="modalDetailContent">
                <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">
                    <i class="fas fa-spinner fa-spin text-indigo-500 text-lg mb-2"></i>
                    <div>Memuat rincian data...</div>
                </div>
            </div>
            <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                    <i class="fas fa-times text-[10px]"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- === INCLUDE MODALS === --}}
@include('jadwal-kerja.create')
@include('jadwal-kerja.update')
@include('jadwal-kerja.import-excel')
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS -->
<script src="{{ asset('js/jadwal-kerja.js') }}"></script>
@endpush