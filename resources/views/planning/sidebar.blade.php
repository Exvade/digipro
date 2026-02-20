@php
$user = Auth::user();
$dashboardRoute = route('menu'); // Default fallback to menu
if ($user) {
if ($user->role === 'leader') {
$dashboardRoute = route('leader.dashboard.inject');
} elseif ($user->role === 'super_admin') {
$dashboardRoute = route('super-admin.dashboard.inject');
} elseif ($user->role === 'kabag') {
$dashboardRoute = route('kabag.dashboard.inject');
} elseif ($user->role === 'manager_plant') {
$dashboardRoute = route('manager-plant.dashboard.inject');
} elseif ($user->role === 'staff') {
$dashboardRoute = route('staff.dashboard.inject');
}
}
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">

    <a href="{{ $dashboardRoute }}" class="brand-link">
        <img src="{{ asset('images/logo2.png') }}" alt="DIGIPRO Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light d-none d-sm-inline">DIGIPRO</span>
        <span class="brand-text font-weight-light d-sm-none">DIGIPRO</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('planning.index') }}" class="nav-link {{ request()->routeIs('planning.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Planning Produksi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('planning.history') }}" class="nav-link {{ request()->routeIs('planning.history') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Riwayat Planning</p>
                    </a>
                </li>
                @if(auth()->user()->role == 'super_admin' || (auth()->user()->role == 'kabag' && (auth()->user()->departemen == 'PPIC' || auth()->user()->departemen == 'Production Planning Inventory Control')))
                <li class="nav-item">
                    <a href="{{ route('planning.approval.page') }}" class="nav-link {{ request()->routeIs('planning.approval.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-check-circle text-success"></i>
                        <p>Approval Planning</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>