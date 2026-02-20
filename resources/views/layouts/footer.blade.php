@php
$hideSidebar = !Auth::check() || request()->routeIs('*.dashboard.inject') || request()->routeIs('*.dashboard.assy') || request()->routeIs('dashboard.inject.public');
@endphp
<footer class="main-footer {{ $hideSidebar ? 'ml-0' : '' }}">
    <!-- To the right -->
    <div class="container text-center">
        <span class="text-muted">© {{ date('Y') }} All rights reserved | IT Department PT Mada Wikri Tunggal</span>
    </div>
</footer>