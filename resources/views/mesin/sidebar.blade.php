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

                {{-- Data Mesin (Always shown in this context) --}}
                <li class="nav-item">
                    <a href="{{ route('mesin.index') }}" class="nav-link {{ request()->routeIs('mesin.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-industry"></i>
                        <p>Data Mesin</p>
                    </a>
                </li>

                {{-- Riwayat Mesin (Leader & Admin) --}}
                @if ($user && (in_array($user->role, ['super_admin', 'manager_plant', 'staff', 'leader', 'kabag']) && !($user->role == 'leader' && $user->bagian == 'assy')))
                <li class="nav-item">
                    <a href="{{ route('dashboard.inject.history') }}"
                        class="nav-link {{ request()->routeIs('dashboard.inject.history') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Riwayat Mesin</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>