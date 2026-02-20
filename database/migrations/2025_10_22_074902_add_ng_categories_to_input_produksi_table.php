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
        Schema::table('input_produksi', function (Blueprint $table) {
            // Add NG category columns to match OEE table structure
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('input_produksi', 'ng_material')) {
                $table->integer('ng_material')->default(0)->comment('NG karena material');
            }
            if (!Schema::hasColumn('input_produksi', 'ng_process')) {
                $table->integer('ng_process')->default(0)->comment('NG karena proses');
            }
            if (!Schema::hasColumn('input_produksi', 'ng_machine')) {
                $table->integer('ng_machine')->default(0)->comment('NG karena mesin');
            }
            if (!Schema::hasColumn('input_produksi', 'ng_operator')) {
                $table->integer('ng_operator')->default(0)->comment('NG karena operator');
            }
            if (!Schema::hasColumn('input_produksi', 'ng_other')) {
                $table->integer('ng_other')->default(0)->comment('NG karena sebab lain');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            // Drop NG category columns only if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('input_produksi', 'ng_material')) {
                $columnsToDrop[] = 'ng_material';
            }
            if (Schema::hasColumn('input_produksi', 'ng_process')) {
                $columnsToDrop[] = 'ng_process';
            }
            if (Schema::hasColumn('input_produksi', 'ng_machine')) {
                $columnsToDrop[] = 'ng_machine';
            }
            if (Schema::hasColumn('input_produksi', 'ng_operator')) {
                $columnsToDrop[] = 'ng_operator';
            }
            if (Schema::hasColumn('input_produksi', 'ng_other')) {
                $columnsToDrop[] = 'ng_other';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
