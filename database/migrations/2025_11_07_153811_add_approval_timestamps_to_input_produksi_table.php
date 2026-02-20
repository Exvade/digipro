<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            
            // Kolom user_id sudah ditambahkan di migration terpisah
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'rejected_at']);
            // $table->dropForeign(['user_id']);
            // $table->dropColumn('user_id');
        });
    }
};