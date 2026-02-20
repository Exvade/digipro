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
        DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesin', function (Blueprint $table) {
            // Revert to enum if necessary, but string is safer now
            // DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason ENUM('setup', 'setdown', 'qc_cek', 'material_habis', 'packaging_habis') NULL");
        });
    }
};
