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
            // Ubah moldshop_id dan mesin_id menjadi nullable
            $table->unsignedBigInteger('moldshop_id')->nullable()->change();
            $table->unsignedBigInteger('mesin_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            // Kembalikan ke NOT NULL jika rollback
            $table->unsignedBigInteger('moldshop_id')->nullable(false)->change();
            $table->unsignedBigInteger('mesin_id')->nullable(false)->change();
        });
    }
};
