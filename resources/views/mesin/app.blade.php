<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'OEE System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        @include('layouts.header')
        @include('mesin.sidebar')
        <div class="content-wrapper">
            @yield('content-header')
            <section class="content pt-3">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>
        @include('mesin.footer')
    </div>
    <!-- AdminLTE & Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Dark Mode JS -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>

    <!-- Global Notifications for Leader & Foreman -->
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