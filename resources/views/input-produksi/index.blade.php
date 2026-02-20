@extends('input-produksi.app')

@section('content')
<div class="container-fluid py-2 px-3">
    {{-- Modern Page Header --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="premium-header p-4 d-flex justify-content-between align-items-center"
                style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="d-flex align-items-center">
                    <div class="header-icon-box mr-4 d-flex align-items-center justify-content-center"
                        style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 18px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-clipboard-list text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 font-weight-bold text-white" style="letter-spacing: -0.5px;">Input Hasil Produksi</h1>
                        <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                            @if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
                            @php
                            $roleDisplay = [
                            'super_admin' => 'Super Admin',
                            'kabag' => 'Kabag',
                            'foreman' => 'Foreman',
                            'leader' => 'Leader'
                            ];
                            @endphp
                            <span class="badge badge-primary px-3 py-2" style="border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-user-shield mr-2"></i>Monitoring Aspect: {{ $roleDisplay[Auth::user()->role] ?? Auth::user()->role }}
                            </span>
                            @else
                            <span class="d-flex align-items-center">
                                <span class="pulse-indicator mr-2"></span>
                                Operator Console &bull; Real-time Input
                            </span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="header-meta d-none d-md-block text-right">
                    <div class="text-white font-weight-bold h5 mb-0" id="current-time-display">--:--:--</div>
                    <div class="text-white-50 small text-uppercase" style="letter-spacing: 2px;">{{ date('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
    @include('input-produksi.admin')
    @else
    @include('input-produksi.operator', ['ngPartTypes' => $ngPartTypes ?? collect()])
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/input-produksi.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@if (in_array(Auth::user()->role, ['super_admin', 'kabag', 'foreman', 'leader']))
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
{{-- Approval Leader CSS --}}
<link rel="stylesheet" href="{{ asset('css/approval-leader.css') }}">
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
<script src="{{ asset('js/approval-leader.js') }}"></script>
@else
<script src="{{ asset('js/input-produksi.js') }}"></script>
@endif

<script>
    function updateDashboardTime() {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0') + ':' +
            now.getSeconds().toString().padStart(2, '0');
        const timeDisplay = document.getElementById('current-time-display');
        if (timeDisplay) timeDisplay.textContent = timeString;
    }
    setInterval(updateDashboardTime, 1000);
    updateDashboardTime();
</script>
@endpush