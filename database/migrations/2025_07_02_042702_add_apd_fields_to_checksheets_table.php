<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            // APD fields
            $table->enum('apd_topi', ['ya', 'tidak'])->nullable()->after('meja_id');
            $table->enum('apd_sepatu', ['ya', 'tidak'])->nullable()->after('apd_topi');
            
            // Quality and instruction fields
            $table->enum('quality_qpoint', ['ya', 'tidak'])->nullable()->after('apd_sepatu');
            $table->enum('instruksi_kerja', ['ya', 'tidak'])->nullable()->after('quality_qpoint');
            $table->enum('limit_sampel', ['ya', 'tidak'])->nullable()->after('instruksi_kerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'apd_topi',
                'apd_sepatu', 
                'quality_qpoint',
                'instruksi_kerja',
                'limit_sampel'
            ]);
        });
    }
};
