<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Tabel Utama: planning_produksi
         */
        Schema::create('planning_produksi', function (Blueprint $table) {
            $table->id();

            // Relasi ke entitas lain
            $table->foreignId('moldshop_id')->constrained('moldshops')->onDelete('cascade');
            $table->foreignId('mesin_id')->constrained('mesin')->onDelete('cascade');
            $table->foreignId('part_id')->nullable()->constrained('parts')->onDelete('cascade');

            // Info utama
            $table->date('tanggal');
            $table->string('lot_produksi');
            $table->integer('qty_planning_produksi'); // total planning per lot

            $table->timestamps();
        });

    }
    public function down(): void
    {
        Schema::dropIfExists('planning_produksi');
    }
};