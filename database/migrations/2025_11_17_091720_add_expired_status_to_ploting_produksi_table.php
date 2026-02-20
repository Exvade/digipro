<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum untuk menambahkan nilai 'expired'
        // MySQL tidak mendukung ALTER ENUM langsung melalui Schema, jadi kita perlu menggunakan raw SQL
        DB::statement("ALTER TABLE `ploting_produksi` MODIFY COLUMN `status` ENUM('waiting', 'on_progress', 'done', 'expired') DEFAULT 'waiting'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum semula tanpa 'expired'
        // Pastikan tidak ada data dengan status 'expired' sebelum rollback
        DB::statement("UPDATE `ploting_produksi` SET `status` = 'done' WHERE `status` = 'expired'");
        DB::statement("ALTER TABLE `ploting_produksi` MODIFY COLUMN `status` ENUM('waiting', 'on_progress', 'done') DEFAULT 'waiting'");
    }
};
