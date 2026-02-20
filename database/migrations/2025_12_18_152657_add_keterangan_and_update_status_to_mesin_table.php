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
        Schema::table('mesin', function (Blueprint $table) {
            // Add keterangan column
            $table->text('keterangan')->nullable()->after('status');
        });

        // Update status enum to include setup and setdown
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown') DEFAULT 'aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesin', function (Blueprint $table) {
            // Remove keterangan column
            $table->dropColumn('keterangan');
        });

        // Revert status enum to original values
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif') DEFAULT 'aktif'");
    }
};
