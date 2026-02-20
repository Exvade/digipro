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
            // Tambah kolom bagian untuk membedakan inject/assy
            $table->enum('bagian', ['inject', 'assy'])->nullable()->after('mesin_id');
            
            // Tambah kolom meja_id untuk assembly (nullable karena inject tidak pakai meja)
            $table->unsignedBigInteger('meja_id')->nullable()->after('bagian');
            $table->foreign('meja_id')->references('id_meja')->on('meja')->onDelete('set null');
            
            // Ubah mesin_id menjadi nullable karena assembly tidak pakai mesin
            $table->unsignedBigInteger('mesin_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->dropForeign(['meja_id']);
            $table->dropColumn(['bagian', 'meja_id']);
            // Kembalikan mesin_id menjadi required jika diperlukan
            // $table->unsignedBigInteger('mesin_id')->nullable(false)->change();
        });
    }
};
