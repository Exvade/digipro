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
        Schema::table('checksheets', function (Blueprint $table) {
            $table->float('suhu_mesin')->nullable()->comment('Suhu Mesin dalam Celcius');
            $table->float('tekanan_mesin')->nullable()->comment('Tekanan Mesin dalam Bar');
            $table->float('getaran_mesin')->nullable()->comment('Tingkat Getaran dalam mm/s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn(['suhu_mesin', 'tekanan_mesin', 'getaran_mesin']);
        });
    }
};
