<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Digital Production System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}?v={{ time() }}" rel="stylesheet">

    <style>
        /* CRITICAL: High-priority override for browser-default password icons */
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

        /* Prevent potential overlap issues */
        .input-group-animated {
            position: relative !important;
        }

        input#password {
            padding-right: 45px !important;
        }
    </style>
</head>

<body class="login-page">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-overlay"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Login Header -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="/images/logo2.png" alt="Logo" class="logo-img">
                </div>
                <h2 class="title-animated">Digital Production System</h2>
                <p class="subtitle-animated">Silahkan Login</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show animated-alert" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show animated-alert" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <!-- Username atau NIK -->
                <div class="form-group form-group-animated">
                    <label for="login" class="form-label">Username atau NIK</label>
                    <div class="input-group input-group-animated">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user icon-animated"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control animated-input @error('login') is-invalid @enderror"
                            id="login" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="Masukkan Username atau NIK">
                    </div>
                    @error('login')
                    <div class="invalid-feedback d-block error-animated">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group form-group-animated">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-animated">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock icon-animated"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control animated-input @error('password') is-invalid @enderror"
                            id="password" name="password" required autocomplete="current-password"
                            spellcheck="false" data-lpignore="true"
                            placeholder="Masukkan Password">
                        <div class="input-group-append">
                            <button class="btn" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    @error('password')
                    <div class="invalid-feedback d-block error-animated">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-login btn-block animated-button">
                    <span class="button-text">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login
                    </span>
                    <div class="button-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </button>

            </form>

            <div class="divider-container my-3">
                <div class="divider-line"></div>
                <span class="divider-text">Atau</span>
                <div class="divider-line"></div>
            </div>

            <!-- Public Dashboard Button -->
            <a href="{{ route('dashboard.inject.public') }}" class="btn btn-outline-primary btn-block animated-button py-2">
                <span class="button-text">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Lihat Dashboard
                </span>
            </a>


            <!-- Footer -->
            <div class="login-footer">
                <p class="text-sm text-gray-500 text-center mb-1">
                    &copy; {{ date('Y') }} Digital Production System
                </p>
                <p class="text-sm text-gray-500 text-center mb-0">
                    IT Department PT Mada Wikri Tunggal
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 4 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="{{ asset('js/login.js') }}?v={{ time() }}"></script>
</body>

</html>