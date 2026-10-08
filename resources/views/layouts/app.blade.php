<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DIGIPRO System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 4.6 CSS (For component/modal compatibility) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <!-- Instant Dark Mode Initialization to prevent flash -->
    <script>
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark', 'dark-mode');
        } else {
            document.documentElement.classList.remove('dark', 'dark-mode');
        }
    </script>

    <!-- Precompiled Tailwind CSS via Vite (Ultra Fast, Zero CPU JIT Lag) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <!-- Custom Dark Mode Helper CSS -->
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}?v={{ filemtime(public_path('css/dark-mode.css')) }}">
</head>

@php
$hideSidebar = !Auth::check() || request()->routeIs('*.dashboard.inject') || request()->routeIs('*.dashboard.assy') || request()->routeIs('dashboard.inject.public');
@endphp

<body class="min-h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-300">
    <div class="min-h-screen flex flex-col">
        @include('layouts.header')

        <div class="flex-1 flex overflow-hidden pt-16">
            @if(!$hideSidebar)
            @include('layouts.sidebar')
            @endif

            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 p-4 sm:p-6 flex flex-col justify-between transition-all duration-300">
                <div>
                    @hasSection('content-header')
                    <div class="mb-6">
                        @yield('content-header')
                    </div>
                    @endif

                    @yield('content')
                </div>

                @include('layouts.footer')
            </main>
        </div>
    </div>

    <!-- Core JS Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Dark Mode JS -->
    <script src="{{ asset('js/dark-mode.js') }}?v={{ filemtime(public_path('css/dark-mode.css')) }}"></script>

    @auth
    @if(in_array(Auth::user()->role, ['leader', 'foreman', 'kabag', 'super_admin']))
    <script>
        window.isLeaderOrForeman = true;
        window.notifUrl = "{{ route('notifications') }}";
    </script>
    <script src="{{ asset('js/notifikasi.js') }}"></script>
    @endif
    @endauth

    @stack('scripts')
</body>

</html>