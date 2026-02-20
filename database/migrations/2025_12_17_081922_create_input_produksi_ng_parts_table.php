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
        Schema::create('input_produksi_ng_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('input_produksi_id')
                ->constrained('input_produksi')
                ->onDelete('cascade');
            $table->foreignId('ng_part_type_id')
                ->constrained('ng_part_types')
                ->onDelete('cascade');
            $table->integer('quantity')->default(0)->comment('Jumlah NG untuk jenis ini');
            $table->string('category')->nullable()->comment('Kategori: material, process, machine, operator, other');
            $table->timestamps();
            
            // Unique constraint: satu input_produksi tidak boleh punya duplikat ng_part_type
            $table->unique(['input_produksi_id', 'ng_part_type_id'], 'unique_input_ng_part');
            $table->index('input_produksi_id');
            $table->index('ng_part_type_id');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_produksi_ng_parts');
    }
};
