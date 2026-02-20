<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menghapus tabel ng_details karena fitur tracking detail NG tidak digunakan.
     * Dashboard OEE menggunakan kolom ng_material, ng_process, dll dari tabel input_produksi.
     */
    public function up(): void
    {
        // Drop tabel (foreign key akan otomatis terhapus)
        // Gunakan dropIfExists untuk menghindari error jika tabel tidak ada
        if (Schema::hasTable('ng_details')) {
            // Drop foreign key constraints terlebih dahulu jika ada
            try {
                Schema::table('ng_details', function (Blueprint $table) {
                    $table->dropForeign(['input_produksi_id']);
                });
            } catch (\Exception $e) {
                // Foreign key mungkin sudah tidak ada atau nama berbeda
            }
            
            try {
                Schema::table('ng_details', function (Blueprint $table) {
                    $table->dropForeign(['assigned_to']);
                });
            } catch (\Exception $e) {
                // Foreign key mungkin sudah tidak ada atau nama berbeda
            }
        }
        
        // Drop tabel
        Schema::dropIfExists('ng_details');
    }

    /**
     * Reverse the migrations.
     * 
     * Jika perlu rollback, buat ulang tabel (tapi fitur tidak digunakan)
     */
    public function down(): void
    {
        Schema::create('ng_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('input_produksi_id');
            $table->unsignedBigInteger('downtime_category_id');
            $table->unsignedBigInteger('downtime_code_id');
            $table->integer('quantity')->comment('Jumlah NG untuk kategori ini');
            $table->text('description')->nullable()->comment('Deskripsi detail NG');
            $table->text('root_cause')->nullable()->comment('Akar penyebab NG');
            $table->text('corrective_action')->nullable()->comment('Tindakan perbaikan');
            $table->text('preventive_action')->nullable()->comment('Tindakan pencegahan');
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('input_produksi_id')->references('id')->on('input_produksi')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['input_produksi_id', 'status']);
            $table->index(['downtime_category_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }
};
