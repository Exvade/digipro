<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('input_produksi', function (Blueprint $table) {
            $table->id();

            // Relasi ke planning_produksi
            $table->foreignId('planning_id')
                  ->constrained('planning_produksi')
                  ->cascadeOnDelete();

            // Kolom untuk mencatat hasil produksi
            $table->integer('ok_count')->default(0); // produk OK
            $table->integer('ng_count')->default(0); // produk NG

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('input_produksi');
    }
};