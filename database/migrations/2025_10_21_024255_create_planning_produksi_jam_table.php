<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_produksi_jam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')
                ->constrained('planning_produksi')
                ->onDelete('cascade');
            $table->enum('shift', ['pagi', 'siang', 'malam']);
            $table->time('jam');
            $table->integer('target')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_produksi_jam');
    }
};
