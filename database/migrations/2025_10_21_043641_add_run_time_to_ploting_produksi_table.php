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
        Schema::table('ploting_produksi', function (Blueprint $table) {
            $table->integer('run_time')->nullable()->after('end_time')->comment('Durasi produksi dalam detik');
        });
    }
    
    public function down(): void
    {
        Schema::table('ploting_produksi', function (Blueprint $table) {
            $table->dropColumn('run_time');
        });
    }
    
};
