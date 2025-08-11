<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add amptm_standar column to checksheets table
        Schema::table('checksheets', function (Blueprint $table) {
            $table->enum('amptm_standar', ['ya', 'tidak'])->nullable()->after('limit_sampel');
        });

        // Add 'amptm' type to checksheet_photos table
        Schema::table('checksheet_photos', function (Blueprint $table) {
            // First, drop the existing enum constraint
            $table->dropColumn('type');
        });

        Schema::table('checksheet_photos', function (Blueprint $table) {
            // Recreate the enum with the new value
            $table->enum('type', ['apd', 'instruksi_kerja', 'qpoint', 'limit_sampel', 'amptm'])->after('checksheet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove amptm_standar column from checksheets table
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn('amptm_standar');
        });

        // Revert checksheet_photos table
        Schema::table('checksheet_photos', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('checksheet_photos', function (Blueprint $table) {
            $table->enum('type', ['apd', 'instruksi_kerja', 'qpoint', 'limit_sampel'])->after('checksheet_id');
        });
    }
};
