@extends('input-produksi.app')

@section('title', 'Input Hasil Produksi - Digital Production System')

@section('content-header')
<div class="flex items-center justify-between gap-4 py-1">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-500/20 flex-shrink-0">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-0">
                Input Hasil Produksi
            </h1>
            @if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
            @php
            $roleDisplay = [
                'super_admin' => 'Super Admin',
                'kabag' => 'Kabag',
                'foreman' => 'Foreman',
                'leader' => 'Leader'
            ];
            @endphp
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/80 inline-flex items-center gap-1.5">
                <i class="fas fa-user-shield text-[10px]"></i>
                <span>{{ $roleDisplay[Auth::user()->role] ?? Auth::user()->role }}</span>
            </span>
            @else
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/80 inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Operator</span>
            </span>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5">
    @if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
    @include('input-produksi.admin')
    @else
    @include('input-produksi.operator', ['ngPartTypes' => $ngPartTypes ?? collect()])
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/input-produksi.css') }}?v={{ filemtime(public_path('css/input-produksi.css')) }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
{{-- Approval Leader CSS --}}
<link rel="stylesheet" href="{{ asset('css/approval-leader.css') }}?v={{ filemtime(public_path('css/approval-leader.css')) }}">
@endif
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

{{-- Routes for JavaScript --}}
<script>
    window.routes = {
        'input-produksi.datatables-data': '{{ route("input-produksi.datatables-data") }}',
        'input-produksi.show': '{{ route("input-produksi.show", ":id") }}',
        'input-produksi.approve': '{{ route("input-produksi.approve", ":id") }}',
        'input-produksi.reject': '{{ route("input-produksi.reject", ":id") }}'
    };
</script>

{{-- Use Approval Leader JS for better UI --}}
<script src="{{ asset('js/approval-leader.js') }}?v={{ filemtime(public_path('js/approval-leader.js')) }}"></script>
@else
<script src="{{ asset('js/input-produksi.js') }}?v={{ filemtime(public_path('js/input-produksi.js')) }}"></script>
@endif

@endpush