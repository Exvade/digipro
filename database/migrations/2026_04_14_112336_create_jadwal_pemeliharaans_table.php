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
        Schema::create('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesin')->onDelete('cascade');
            $table->integer('tahun');
            $table->integer('bulan'); // 1 - 12
            $table->integer('minggu'); // 1 - 5
            $table->string('plan_tipe', 5)->nullable()->comment('S (Small), M (Medium), O (Overhaul)');
            $table->string('aktual_tanggal')->nullable()->comment('Bisa diisi tanggal atau tanda centang');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Mencegah duplikasi data per minggu per mesin
            $table->unique(['mesin_id', 'tahun', 'bulan', 'minggu'], 'jadwal_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pemeliharaans');
    }
};
