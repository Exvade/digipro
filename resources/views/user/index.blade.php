@extends('layouts.app')

@section('title', 'Data Master User - Digital Production System')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Custom User CSS -->
<link rel="stylesheet" href="{{ asset('css/user.css') }}?v={{ filemtime(public_path('css/user.css')) }}">
@endpush

@section('content-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-500/25 flex-shrink-0">
            <i class="fas fa-users-cog"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-0">
                    Data Master User
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>{{ $users->count() }} Pengguna</span>
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 mb-0">
                Kelola akun pengguna, peran hak akses, departemen, dan grup kerja sistem DIGIPRO
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" 
                onclick="openCreateModal()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
            <i class="fas fa-user-plus text-xs"></i>
            <span>Tambah User</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Statistics KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total User --}}
        <div class="kpi-card kpi-card-indigo active bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Total Pengguna
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-slate-900 dark:text-white" data-target="{{ $users->count() }}">
                        {{ number_format($users->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">User</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-b-2xl"></div>
        </div>

        {{-- Dengan Grup --}}
        <div class="kpi-card kpi-card-emerald bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="with-group">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Dengan Grup
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" data-target="{{ $users->where('grup_id', '!=', null)->count() }}">
                        {{ number_format($users->where('grup_id', '!=', null)->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Terpetakan</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Tanpa Grup --}}
        <div class="kpi-card kpi-card-amber bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="no-group">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Tanpa Grup
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-amber-500 dark:text-amber-400" data-target="{{ $users->where('grup_id', null)->count() }}">
                        {{ number_format($users->where('grup_id', null)->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Belum Ada</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 text-amber-500 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600 rounded-b-2xl hidden"></div>
        </div>

        {{-- Grup Aktif --}}
        <div class="kpi-card kpi-card-sky bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm relative overflow-hidden flex items-center justify-between cursor-pointer group transition-all" data-filter="groups">
            <div class="space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Grup Terpakai
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="kpi-counter text-2xl font-black font-mono text-sky-600 dark:text-sky-400" data-target="{{ $users->pluck('grup_id')->unique()->filter()->count() }}">
                        {{ number_format($users->pluck('grup_id')->unique()->filter()->count()) }}
                    </span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Regu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 kpi-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="kpi-active-indicator absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-sky-600 rounded-b-2xl hidden"></div>
        </div>
    </div>

    {{-- Session Alerts --}}
    @if (session('success'))
    <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm shadow-sm transition-all" role="alert">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-xs"></i>
        </div>
        <div class="flex-1 font-medium">
            {{ session('success') }}
        </div>
        <button type="button" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm shadow-sm transition-all" role="alert">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-xs"></i>
        </div>
        <div class="flex-1 font-medium">
            {{ session('error') }}
        </div>
        <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 p-1" data-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-table-list"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0">Daftar Akun Pengguna</h2>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Semua pengguna yang terdaftar di sistem</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Total: {{ $users->count() }} Data
            </span>
        </div>

        <div class="p-4 sm:p-5">
            <table id="userTable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">No</th>
                            <th>NIK</th>
                            <th>Pengguna</th>
                            <th>Departemen</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Grup</th>
                            <th>Bagian</th>
                            <th class="text-center" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                        <tr>
                            <td class="text-center font-mono text-xs text-slate-400 dark:text-slate-500">{{ $index + 1 }}</td>
                            <td>
                                <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $user->nik ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/80 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->nama, 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $user->username }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">
                                    {{ $user->departemen ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $user->nama }}
                                </span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $user->email ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @switch($user->role)
                                    @case('super_admin')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 flex-shrink-0"></span>
                                            <span>Super Admin</span>
                                        </span>
                                        @break

                                    @case('leader')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                            <span>Leader</span>
                                        </span>
                                        @break

                                    @case('operator')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 flex-shrink-0"></span>
                                            <span>Operator</span>
                                        </span>
                                        @break

                                    @case('foreman')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                            <span>Foreman</span>
                                        </span>
                                        @break

                                    @case('kabag')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 flex-shrink-0"></span>
                                            <span>Kabag</span>
                                        </span>
                                        @break

                                    @case('staff')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0"></span>
                                            <span>Staff</span>
                                        </span>
                                        @break

                                    @case('manager_plant')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                            <span>Manager Plant</span>
                                        </span>
                                        @break

                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium whitespace-nowrap bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            <span>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                                        </span>
                                @endswitch
                            </td>
                            <td data-search="{{ $user->grup_id && $user->grup ? 'with-group ' . $user->grup->nama_grup : 'no-group -' }}">
                                @if ($user->grup_id && $user->grup)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold whitespace-nowrap bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <i class="fas fa-layer-group text-[9px] text-slate-400 dark:text-slate-500"></i>
                                        <span>{{ $user->grup->nama_grup }}</span>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->bagian)
                                    @if ($user->bagian == 'inject')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                                            INJECT
                                        </span>
                                    @elseif($user->bagian == 'assy')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/60">
                                            ASSY
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold whitespace-nowrap bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ strtoupper($user->bagian) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="Detail User"
                                            data-toggle="tooltip"
                                            onclick="openDetailModal('{{ $user->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/50 dark:hover:bg-sky-900/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="Edit User"
                                            data-toggle="tooltip"
                                            onclick="openEditModal('{{ $user->id }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if ($user->id !== auth()->id())
                                    <button type="button" 
                                            class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="Hapus User"
                                            data-toggle="tooltip"
                                            onclick="deleteUser('{{ $user->id }}', '{{ $user->nama }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="addModalLabel">
                            Tambah Pengguna Baru
                        </h5>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Isi formulir data pengguna dan hak akses</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-all" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="addModalBody">
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl text-indigo-500 mb-2"></i>
                    <span class="text-xs">Memuat formulir...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="detailModalLabel">
                            Detail Akun Pengguna
                        </h5>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Informasi lengkap profil dan riwayat aktivitas</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-all" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="detailModalBody">
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl text-indigo-500 mb-2"></i>
                    <span class="text-xs">Memuat detail pengguna...</span>
                </div>
            </div>
            <div class="modal-footer px-6 py-3 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex justify-end">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all" data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/70 border border-sky-100 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="editModalLabel">
                            Edit Data Pengguna
                        </h5>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mb-0">Perbarui profil, peran, atau kata sandi</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-all" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="editModalBody">
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl text-sky-500 mb-2"></i>
                    <span class="text-xs">Memuat formulir edit...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom User JS -->
<script src="{{ asset('js/user.js') }}?v={{ filemtime(public_path('js/user.js')) }}"></script>
<script>
    // Make route URLs available to JavaScript
    window.createModalUrl = "{{ route('user.create') }}";
</script>
@endpush