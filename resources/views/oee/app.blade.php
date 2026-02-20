<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'OEE Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    
    <!-- Critical Dark Mode CSS - Inline to prevent flash -->
    <style>
        /* Apply dark mode immediately if enabled */
        html.dark-mode,
        html.dark-mode body {
            background-color: #1a1a1a !important;
            color: #e0e0e0 !important;
        }
        
        /* Disable all transitions during page load */
        html.preload *,
        html.preload *:before,
        html.preload *:after {
            transition: none !important;
            animation: none !important;
        }
    </style>
    
    <!-- Dark Mode Pre-loader Script - Must run BEFORE any rendering -->
    <script>
        // IIFE to apply dark mode synchronously before page renders
        (function() {
            'use strict';
            
            // Add preload class immediately
            var html = document.documentElement;
            html.classList.add('preload');
            
            // Check localStorage and apply dark mode immediately
            try {
                var isDarkMode = localStorage.getItem('darkMode') === 'enabled';
                if (isDarkMode) {
                    html.classList.add('dark-mode');
                    // Set inline style as extra insurance
                    html.style.backgroundColor = '#1a1a1a';
                    document.body && (document.body.style.backgroundColor = '#1a1a1a');
                }
            } catch (e) {
                // localStorage might not be available
                console.warn('Dark mode: localStorage not available');
            }
            
            // Remove preload class after DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        html.classList.remove('preload');
                    }, 50);
                });
            } else {
                setTimeout(function() {
                    html.classList.remove('preload');
                }, 50);
            }
        })();
    </script>
    
    <!-- Custom OEE Styles -->
    <link href="{{ asset('css/oee-dashboard.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Dark Mode CSS -->
    <link href="{{ asset('css/dark-mode.css') }}?v={{ time() }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Header -->
    @include('oee.header')
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            @hasSection('content-header')
                @yield('content-header')
            @endif
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
   <footer class="main-footer">
    <!-- To the right -->
    <div class="container text-center">
        <span class="text-muted">© {{ date('Y') }} All rights reserved | IT Department PT Mada Wikri Tunggal</span>
    </div>
</footer> 
    
    <!-- Bootstrap 4 & jQuery JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <!-- Dark Mode JS -->
    <script src="{{ asset('js/dark-mode.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html>
