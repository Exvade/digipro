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
        DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason ENUM('setup', 'setdown', 'qc_cek', 'material_habis', 'packaging_habis') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No change needed or revert to whatever it was before (mostly setup, setdown, material_habis, packaging_habis)
        DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason ENUM('setup', 'setdown', 'material_habis', 'packaging_habis') NULL");
    }
};
