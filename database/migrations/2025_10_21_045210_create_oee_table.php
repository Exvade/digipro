<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CATATAN PENTING:
     * Tabel oee_records ini hanya digunakan sebagai BACKUP/ARCHIVE data OEE.
     * Dashboard OEE menghitung data secara REAL-TIME dari tabel planning_produksi dan input_produksi.
     * Data di tabel ini di-generate otomatis saat InputProduksi di-approve.
     * Jika ingin menghapus tabel ini, pastikan method generateOeeRecord() di InputProduksiController juga dihapus.
     */
    public function up(): void
    {
        Schema::create('oee_records', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('mesin_id');
            $table->unsignedBigInteger('planning_id');
            $table->date('tanggal');
            
            // Waktu operasi
            $table->integer('planned_time')->comment('waktu rencana (menit)');
            $table->integer('run_time')->comment('waktu aktual mesin berjalan (menit)');
            $table->decimal('cycle_time_std', 10, 2)->comment('detik/siklus ideal');
            $table->decimal('cycle_time_actual', 10, 2)->comment('detik/siklus aktual');
            $table->integer('cavity_mold');
            
            // Output produksi
            $table->integer('actual_output')->comment('total produk (OK + NG)');
            $table->integer('good_output')->comment('produk OK');
            $table->integer('ng_output')->comment('produk NG total');
            
            // NG per kategori (untuk analisis detail)
            $table->integer('ng_material')->default(0)->comment('NG karena material');
            $table->integer('ng_process')->default(0)->comment('NG karena proses');
            $table->integer('ng_machine')->default(0)->comment('NG karena mesin');
            $table->integer('ng_operator')->default(0)->comment('NG karena operator');
            $table->integer('ng_other')->default(0)->comment('NG karena lainnya');
            
            // Rate OEE
            $table->decimal('availability_rate', 5, 2);
            $table->decimal('performance_rate', 5, 2);
            $table->decimal('quality_rate', 5, 2);
            $table->decimal('oee_rate', 5, 2);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('mesin_id')
                  ->references('id')
                  ->on('mesin')
                  ->onDelete('cascade');
                  
            $table->foreign('planning_id')
                  ->references('id')
                  ->on('planning_produksi')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oee_records');
    }
};