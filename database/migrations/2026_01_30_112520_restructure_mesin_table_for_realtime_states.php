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
        // 1. Add status_since and stop_reason columns
        Schema::table('mesin', function (Blueprint $table) {
            if (!Schema::hasColumn('mesin', 'status_since')) {
                $table->dateTime('status_since')->nullable()->after('tonase_mesin');
            }
            if (!Schema::hasColumn('mesin', 'stop_reason')) {
                // Temporary broaden to allow all possible reasons
                $table->string('stop_reason')->nullable()->after('status');
            }
        });

        // 2. Expand Status Enum temporarily to include ALL old and new values
        // This is necessary so we can update records without violating enum constraints
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown', 'material_habis', 'sarana_packaging_habis', 'running', 'stop', 'offline') DEFAULT 'tidak aktif'");

        // 3. Map existing status to new structure

        // Map reasons
        DB::table('mesin')->where('status', 'setup')->update(['stop_reason' => 'setup']);
        DB::table('mesin')->where('status', 'setdown')->update(['stop_reason' => 'setdown']);
        DB::table('mesin')->where('status', 'material_habis')->update(['stop_reason' => 'material_habis']);
        DB::table('mesin')->where('status', 'sarana_packaging_habis')->update(['stop_reason' => 'packaging_habis']);

        // Set status_since for everyone to now initially
        DB::table('mesin')->whereNull('status_since')->update(['status_since' => now()]);

        // Map status
        DB::table('mesin')->whereIn('status', ['setup', 'setdown', 'material_habis', 'sarana_packaging_habis'])->update(['status' => 'stop']);
        DB::table('mesin')->where('status', 'aktif')->update(['status' => 'running']);
        DB::table('mesin')->where('status', 'tidak aktif')->update(['status' => 'offline']);
        // 'maintenance' stays 'maintenance'

        // 4. Finalize ENUMs
        // Final Status ENUM
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('running', 'stop', 'maintenance', 'offline') DEFAULT 'offline'");

        // Final Stop Reason ENUM
        DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason ENUM('setup', 'setdown', 'qc_cek', 'material_habis', 'packaging_habis') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Expand back to allow all values
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown', 'material_habis', 'sarana_packaging_habis', 'running', 'stop', 'offline') DEFAULT 'tidak aktif'");
        DB::statement("ALTER TABLE mesin MODIFY COLUMN stop_reason VARCHAR(255) NULL");

        // Map back
        DB::table('mesin')->where('status', 'running')->update(['status' => 'aktif']);
        DB::table('mesin')->where('status', 'offline')->update(['status' => 'tidak aktif']);
        DB::table('mesin')->where('status', 'stop')->where('stop_reason', 'setup')->update(['status' => 'setup']);
        DB::table('mesin')->where('status', 'stop')->where('stop_reason', 'setdown')->update(['status' => 'setdown']);
        DB::table('mesin')->where('status', 'stop')->where('stop_reason', 'material_habis')->update(['status' => 'material_habis']);
        DB::table('mesin')->where('status', 'stop')->where('stop_reason', 'packaging_habis')->update(['status' => 'sarana_packaging_habis']);

        // Finalize old state (if possible - might need adjustment based on original state)
        DB::statement("ALTER TABLE mesin MODIFY COLUMN status ENUM('aktif', 'maintenance', 'tidak aktif', 'setup', 'setdown', 'material_habis', 'sarana_packaging_habis') DEFAULT 'aktif'");

        Schema::table('mesin', function (Blueprint $table) {
            $table->dropColumn(['status_since', 'stop_reason']);
        });
    }
};
