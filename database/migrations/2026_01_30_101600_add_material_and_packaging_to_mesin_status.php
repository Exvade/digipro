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
        // Update status enum to include material_habis and sarana_packaging_habis
        // Note: We must include ALL existing enum values in the new definition
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown', 'material_habis', 'sarana_packaging_habis') DEFAULT 'aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum to previous values
        // Note: This might fail if there are records with the new statuses
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown') DEFAULT 'aktif'");
    }
};
