<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menghapus kolom-kolom yang tidak digunakan di tabel input_produksi.
     * Kolom-kolom ini tidak digunakan di aplikasi dan hanya mengisi database dengan NULL.
     * 
     * Kolom yang dihapus:
     * - ng_category (hanya digunakan sedikit di laporan, tidak penting)
     * - ng_code (tidak digunakan)
     * - ng_category_id (relasi tidak digunakan)
     * - ng_code_id (relasi tidak digunakan)
     * - corrective_action (tidak digunakan)
     * - preventive_action (tidak digunakan)
     * - ng_status (scope/accessor ada tapi tidak digunakan)
     * - assigned_to (relasi ada tapi tidak digunakan)
     * - resolved_at (tidak digunakan)
     * 
     * CATATAN: ng_description TIDAK dihapus karena digunakan untuk rejection reason.
     */
    public function up(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            // Hapus kolom yang tidak digunakan
            $columnsToDrop = [];
            
            // Cek dan hapus kolom jika ada
            if (Schema::hasColumn('input_produksi', 'ng_category')) {
                $columnsToDrop[] = 'ng_category';
            }
            if (Schema::hasColumn('input_produksi', 'ng_code')) {
                $columnsToDrop[] = 'ng_code';
            }
            if (Schema::hasColumn('input_produksi', 'ng_category_id')) {
                $columnsToDrop[] = 'ng_category_id';
            }
            if (Schema::hasColumn('input_produksi', 'ng_code_id')) {
                $columnsToDrop[] = 'ng_code_id';
            }
            if (Schema::hasColumn('input_produksi', 'corrective_action')) {
                $columnsToDrop[] = 'corrective_action';
            }
            if (Schema::hasColumn('input_produksi', 'preventive_action')) {
                $columnsToDrop[] = 'preventive_action';
            }
            if (Schema::hasColumn('input_produksi', 'ng_status')) {
                $columnsToDrop[] = 'ng_status';
            }
            if (Schema::hasColumn('input_produksi', 'assigned_to')) {
                // Hapus foreign key terlebih dahulu jika ada
                // Cek foreign key dengan query langsung karena Laravel tidak punya method untuk cek foreign key
                try {
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'input_produksi' 
                        AND COLUMN_NAME = 'assigned_to' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    foreach ($foreignKeys as $fk) {
                        try {
                            // Drop foreign key menggunakan nama constraint
                            DB::statement("ALTER TABLE `input_produksi` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                        } catch (\Exception $e) {
                            // Foreign key mungkin sudah tidak ada, lanjutkan
                        }
                    }
                } catch (\Exception $e) {
                    // Query mungkin gagal, lanjutkan tanpa drop foreign key
                }
                
                $columnsToDrop[] = 'assigned_to';
            }
            if (Schema::hasColumn('input_produksi', 'resolved_at')) {
                $columnsToDrop[] = 'resolved_at';
            }
            
            // Hapus kolom jika ada
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Jika perlu rollback, buat ulang kolom-kolom (tapi tidak digunakan)
     */
    public function down(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            // Tambahkan kembali kolom jika perlu rollback
            $table->string('ng_category', 50)->nullable()->after('ng_description');
            $table->string('ng_code', 20)->nullable()->after('ng_category');
            $table->unsignedBigInteger('ng_category_id')->nullable()->after('ng_code');
            $table->unsignedBigInteger('ng_code_id')->nullable()->after('ng_category_id');
            $table->text('corrective_action')->nullable()->after('ng_code_id');
            $table->text('preventive_action')->nullable()->after('corrective_action');
            $table->enum('ng_status', ['open', 'investigating', 'resolved', 'closed'])->default('open')->after('preventive_action');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('ng_status');
            $table->timestamp('resolved_at')->nullable()->after('assigned_to');
            
            // Foreign key untuk assigned_to
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }
};
