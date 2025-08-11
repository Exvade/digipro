@extends('layouts.welcome')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="https://ui-avatars.com/api/?name=DP&background=2e59d9&color=fff&size=32&rounded=true" alt="Logo" class="d-inline-block align-text-top me-2">
                Digital Production
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item">
                                <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="nav-link">Log in</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a href="{{ route('register') }}" class="btn btn-primary ms-lg-2">Register</a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <img src="https://ui-avatars.com/api/?name=Digital+Production&background=2e59d9&color=fff" alt="Logo" class="rounded-circle mb-4" style="width: 100px; height: 100px; box-shadow: 0 4px 12px rgba(46, 89, 217, 0.3);">
                <h1>Digital Production System</h1>
                <p>Streamline your production process with our advanced digital solutions. Boost efficiency, reduce errors, and gain real-time insights.</p>
                <div class="welcome-buttons">
                    <a href="/login" class="welcome-btn welcome-btn-primary">Get Started</a>
                    <a href="#features" class="welcome-btn welcome-btn-secondary">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="container">
                <h2 class="section-title">Why Choose Us?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-cogs"></i>
                        <h3>Streamlined Workflow</h3>
                        <p>Automate repetitive tasks and optimize your entire production line for maximum efficiency.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Real-time Analytics</h3>
                        <p>Access up-to-the-minute data and dashboards to make informed decisions quickly.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-check-circle"></i>
                        <h3>Quality Control</h3>
                        <p>Improve product quality with integrated checks and balances throughout the process.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        Digital Production System v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </footer>
@endsection