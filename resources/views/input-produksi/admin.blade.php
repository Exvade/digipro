{{-- ==================== SUPER ADMIN / KABAG / FOREMAN / LEADER VIEW - APPROVAL DASHBOARD ==================== --}}
{{-- Replaces old DataTable view with Approval Leader Dashboard --}}

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <form id="dateFilterForm" class="form-inline">
                            <div class="form-group mr-3 mb-2 mb-md-0">
                                <label for="start_date" class="mr-2 font-weight-bold">Periode:</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group mr-3 mb-2 mb-md-0">
                                <label for="end_date" class="mr-2 font-weight-bold">s/d</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <button type="button" id="btnApplyDateFilter" class="btn btn-primary btn-sm px-3">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <button type="button" id="btnResetDateFilter" class="btn btn-secondary btn-sm ml-2 px-3">
                                <i class="fas fa-sync mr-1"></i> Reset
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-md-right mt-2 mt-md-0">
                        <span class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Menampilkan data berdasarkan tanggal pembuatan.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle mr-2"></i>
            Pastikan semua input produksi telah diajukan untuk ditinjau.
        </div>
    </div>
</div>

{{-- Tab Filter Section --}}
@php
$user = Auth::user();
$isLeaderInject = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'inject');
$isLeaderAssy = ($user->role === 'leader' && isset($user->bagian) && strtolower($user->bagian) === 'assy');
@endphp

@if(!$isLeaderInject && !$isLeaderAssy)
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <div class="tab-filter-wrapper">
                    <ul class="nav nav-pills nav-fill bagian-tabs flex-column flex-md-row" id="bagianTabsApprovalLeader" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab-approval-leader" href="#" data-bagian="all">
                                <i class="fas fa-list"></i> All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inject-tab-approval-leader" href="#" data-bagian="inject">
                                <i class="fas fa-cog"></i> Inject
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" id="assy-tab-approval-leader" href="#" data-bagian="assy">
                                <i class="fas fa-tools"></i> Assy
                            </a>
                        </li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4 tabs-scrollable" id="approvalTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
            <i class="fas fa-clock mr-2"></i>
            Pending <span class="badge badge-warning ml-2" id="pendingCount">0</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab">
            <i class="fas fa-check-circle mr-2"></i>
            Approved <span class="badge badge-success ml-2" id="approvedCount">0</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
            <i class="fas fa-times-circle mr-2"></i>
            Rejected <span class="badge badge-danger ml-2" id="rejectedCount">0</span>
        </a>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="approvalTabsContent">
    <!-- Pending Tab -->
    <div class="tab-pane fade show active" id="pending" role="tabpanel">
        <!-- Shift 1 Section -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-sun mr-2"></i>Shift 1 (Pagi) - Data Pending Approval
                    <span class="badge badge-light ml-2" id="pendingShift1Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="pendingTableShift1" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift 2 Section -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-cloud-sun mr-2"></i>Shift 2 (Siang) - Data Pending Approval
                    <span class="badge badge-light ml-2" id="pendingShift2Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="pendingTableShift2" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift 3 Section -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-moon mr-2"></i>Shift 3 (Malam) - Data Pending Approval
                    <span class="badge badge-light ml-2" id="pendingShift3Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="pendingTableShift3" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Tab -->
    <div class="tab-pane fade" id="approved" role="tabpanel">
        <!-- Shift 1 Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-sun mr-2"></i>Shift 1 (Pagi) - Data Approved
                    <span class="badge badge-light ml-2" id="approvedShift1Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="approvedTableShift1" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift 2 Section -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-cloud-sun mr-2"></i>Shift 2 (Siang) - Data Approved
                    <span class="badge badge-light ml-2" id="approvedShift2Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="approvedTableShift2" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift 3 Section -->
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-moon mr-2"></i>Shift 3 (Malam) - Data Approved
                    <span class="badge badge-light ml-2" id="approvedShift3Count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="approvedTableShift3" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th style="width: 5%">Target</th>
                                <th style="width: 5%">Eff</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejected Tab -->
    <div class="tab-pane fade" id="rejected" role="tabpanel">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-times-circle mr-2"></i>Data Rejected</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover approval-table align-middle" id="rejectedTable" style="width: 100%">
                        <thead class="text-center text-nowrap">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th style="width: 10%">Tanggal</th>
                                <th style="width: 5%">Bagian</th>
                                <th style="width: 15%">Operator</th>
                                <th style="width: 5%">Grup</th>
                                <th style="width: 15%">Part</th>
                                <th style="width: 8%">Mold/Meja</th>
                                <th style="width: 8%">Mesin/Meja</th>
                                <th style="width: 5%">OK</th>
                                <th style="width: 5%">NG</th>
                                <th style="width: 5%">Total</th>
                                <th>Alasan</th>
                                <th>Rejected By</th>
                                <th>Rejected At</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('approval-leader.modals')

<div id="approval-leader-config"
    data-is-leader-inject="{{ $isLeaderInject ? 'true' : 'false' }}"
    data-is-leader-assy="{{ $isLeaderAssy ? 'true' : 'false' }}">
</div>

@push('scripts')
{{-- Tab Filter Script --}}
<script>
    $(document).ready(function() {
        const config = $('#approval-leader-config');
        const isLeaderInject = config.data('is-leader-inject') === true;
        const isLeaderAssy = config.data('is-leader-assy') === true;

        // Hanya jalankan jika tab filter ada (bukan leader dengan bagian spesifik)
        if ($('#bagianTabsApprovalLeader').length > 0) {
            $('#bagianTabsApprovalLeader .nav-link').on('click', function(e) {
                e.preventDefault();
                const bagian = $(this).data('bagian');

                // Update active tab
                $('#bagianTabsApprovalLeader .nav-link').removeClass('active');
                $(this).addClass('active');

                if (typeof setBagianFilter === 'function') {
                    setBagianFilter(bagian);
                }
            });
        } else {
            // Untuk leader dengan bagian spesifik, set filter otomatis
            if (isLeaderInject) {
                if (typeof setBagianFilter === 'function') {
                    setBagianFilter('inject');
                }
            } else if (isLeaderAssy) {
                if (typeof setBagianFilter === 'function') {
                    setBagianFilter('assy');
                }
            }
        }
    });
</script>
@endpush