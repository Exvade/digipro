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
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled'])
                  ->default('draft')
                  ->after('qty_planning_produksi');
        });
    }
};
