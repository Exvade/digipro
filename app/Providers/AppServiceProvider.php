<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon; // ✅ Tambahkan ini
use Illuminate\Support\Facades\DB; // ✅ Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale('id');
    
        // ✅ Pastikan timezone PHP sesuai (WIB)
        date_default_timezone_set('Asia/Jakarta');
    
        // ✅ Pastikan timezone MySQL juga WIB
        DB::statement("SET time_zone = '+07:00'");
    }
    
}
