<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Digital Production System</title>

    <!-- Instant Dark Mode Initialization to prevent flash -->
    <script>
        (function() {
            var isDark = localStorage.getItem('darkMode') === 'enabled';
            if (isDark) {
                document.documentElement.classList.add('dark', 'dark-mode');
            } else {
                document.documentElement.classList.remove('dark', 'dark-mode');
            }
        })();
    </script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Precompiled Tailwind CSS via Vite (Ultra Fast) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Hide Microsoft Edge / Chrome default password reveal icons */
        input#password::-ms-reveal,
        input#password::-ms-clear,
        input#password::-webkit-password-reveal,
        input#password::-webkit-clear-button,
        input#password::-webkit-credentials-auto-fill-button {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Smooth theme transition */
        * {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white flex flex-col justify-between p-4 sm:p-6 relative overflow-x-hidden">
    
    <!-- Ambient Factory Background Image -->
    <div class="fixed inset-0 -z-20 bg-cover bg-center bg-no-repeat bg-fixed opacity-10 dark:opacity-15 pointer-events-none" style="background-image: url('{{ asset('images/bg-mwt.jpg') }}');"></div>
    
    <!-- Gradient Ambient Overlays -->
    <div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50/90 via-slate-100/95 to-sky-50/90 dark:from-slate-950/95 dark:via-slate-900/95 dark:to-indigo-950/90 pointer-events-none"></div>
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_15%_15%,rgba(99,102,241,0.15),transparent_45%),radial-gradient(circle_at_85%_85%,rgba(14,165,233,0.12),transparent_45%)] pointer-events-none"></div>

    <!-- Top Floating Header Bar -->
    <header class="w-full max-w-4xl mx-auto flex items-center justify-between z-10 py-1">
        <!-- Logo Pill Badge -->
        <div class="flex items-center gap-2.5 px-3.5 py-1.5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-full shadow-sm">
            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-xs shadow-sm">
                <i class="fas fa-industry"></i>
            </div>
            <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-1.5">
                DIGIPRO <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/60 px-1.5 py-0.5 rounded-md">SYSTEM</span>
            </span>
        </div>

        <!-- Modern Clean Theme Switcher Pill -->
        <button id="themeToggleBtn" type="button" class="group relative flex items-center gap-2 px-3 py-1.5 bg-white/90 dark:bg-slate-900/90 hover:bg-white dark:hover:bg-slate-800 backdrop-blur-md border border-slate-200/90 dark:border-slate-700/80 rounded-full shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer" title="Ganti Mode Terang / Gelap (Ctrl+D)" aria-label="Toggle Theme">
            <span class="w-6 h-6 rounded-full flex items-center justify-center bg-amber-100 text-amber-600 dark:bg-indigo-950/80 dark:text-amber-400 shadow-sm transition-transform duration-200 group-hover:rotate-12">
                <i class="fas fa-sun text-xs dark:hidden"></i>
                <i class="fas fa-moon text-xs hidden dark:inline text-indigo-300"></i>
            </span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 pr-1 select-none">
                <span class="dark:hidden">Light</span>
                <span class="hidden dark:inline">Dark</span>
            </span>
        </button>
    </header>

    <!-- Main Content Container -->
    <main class="w-full max-w-md mx-auto my-auto py-4 sm:py-6 z-10">
        <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 dark:shadow-2xl dark:shadow-slate-950/70 transition-all duration-300">
            
            <!-- Login Header & Branding -->
            <div class="text-center mb-6">
                <div class="inline-flex p-1.5 bg-white dark:bg-slate-800 rounded-full shadow-md border border-slate-100 dark:border-slate-700/60 mb-3.5 group hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo bulat.png') }}" alt="Logo PT Mada Wikri Tunggal" class="w-16 h-16 sm:w-20 sm:h-20 object-contain rounded-full">
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Digital Production System
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                    PT Mada Wikri Tunggal • Silakan Masuk
                </p>
            </div>

            <!-- Session Status Alert -->
            @if (session('status'))
            <div class="mb-5 flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                <i class="fas fa-check-circle text-base text-emerald-500 flex-shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            <!-- Validation Errors Alert -->
            @if ($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 rounded-2xl text-rose-700 dark:text-rose-300 text-xs font-semibold">
                <div class="flex items-center gap-2 mb-1.5 text-rose-800 dark:text-rose-200 font-bold">
                    <i class="fas fa-exclamation-triangle text-sm text-rose-500 flex-shrink-0"></i>
                    <span>Login Gagal</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-[11px] opacity-90 pl-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
                @csrf

                <!-- Username atau NIK -->
                <div>
                    <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Username atau NIK
                    </label>
                    <div class="relative rounded-2xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <input type="text"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            placeholder="Masukkan Username atau NIK"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/70 border {{ $errors->has('login') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/20' }} rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-4 transition-all duration-200">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Password
                    </label>
                    <div class="relative rounded-2xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            spellcheck="false"
                            data-lpignore="true"
                            placeholder="Masukkan Password Anda"
                            class="w-full pl-10 pr-12 py-3 bg-slate-50 dark:bg-slate-800/70 border {{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/20' }} rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-4 transition-all duration-200">
                        
                        <!-- Toggle Password Button -->
                        <button type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3.5 pl-2 flex items-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan password">
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:focus:ring-indigo-400 dark:bg-slate-800 cursor-pointer">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                        id="loginSubmitBtn"
                        class="w-full flex items-center justify-center gap-2.5 py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer">
                        <span id="btnText" class="flex items-center gap-2">
                            <i class="fas fa-sign-in-alt text-sm"></i>
                            <span>Masuk ke Sistem</span>
                        </span>
                        <span id="btnSpinner" class="hidden items-center gap-2">
                            <i class="fas fa-spinner fa-spin text-sm"></i>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider">
                    <span class="bg-white dark:bg-slate-900 px-3 text-slate-400 dark:text-slate-500 font-bold">
                        Akses Cepat
                    </span>
                </div>
            </div>

            <!-- Public Dashboard Access Button -->
            <a href="{{ route('dashboard.inject.public') }}"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700/80 hover:border-indigo-200 dark:hover:border-indigo-800/80 rounded-2xl font-bold text-xs sm:text-sm transition-all duration-200 shadow-sm">
                <i class="fas fa-chart-pie text-indigo-500"></i>
                <span>Lihat Dashboard Monitoring</span>
            </a>

            <!-- Card Footer -->
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center">
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                    &copy; {{ date('Y') }} DIGIPRO • IT Department PT Mada Wikri Tunggal
                </p>
            </div>
        </div>
    </main>

    <!-- Footer Note -->
    <footer class="w-full text-center py-2 z-10">
        <p class="text-[11px] text-slate-500 dark:text-slate-400 tracking-wide font-medium">
            Sistem Informasi Monitoring Produksi & Preventive Terpadu
        </p>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password Visibility Toggle
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePasswordBtn && passwordInput && eyeIcon) {
                togglePasswordBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    
                    if (isPassword) {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                        eyeIcon.classList.add('text-indigo-600', 'dark:text-indigo-400');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                        eyeIcon.classList.remove('text-indigo-600', 'dark:text-indigo-400');
                    }
                });
            }

            // Button Loading State on Form Submit
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('loginSubmitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            if (loginForm && submitBtn && btnText && btnSpinner) {
                loginForm.addEventListener('submit', function() {
                    btnText.classList.add('hidden');
                    btnSpinner.classList.remove('hidden');
                    btnSpinner.classList.add('flex');
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                });
            }

            // Theme Controller (Light/Dark Mode)
            const themeToggleBtn = document.getElementById('themeToggleBtn');

            function applyTheme(isDark) {
                const html = document.documentElement;
                const body = document.body;

                if (isDark) {
                    html.classList.add('dark', 'dark-mode');
                    if (body) body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    html.classList.remove('dark', 'dark-mode');
                    if (body) body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            }

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isCurrentlyDark = document.documentElement.classList.contains('dark');
                    applyTheme(!isCurrentlyDark);
                });
            }

            // Keyboard shortcut: Ctrl+D or Cmd+D to toggle dark mode
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
                    e.preventDefault();
                    const isCurrentlyDark = document.documentElement.classList.contains('dark');
                    applyTheme(!isCurrentlyDark);
                }
            });
        });
    </script>
</body>

</html>