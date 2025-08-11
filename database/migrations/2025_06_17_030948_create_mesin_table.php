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
        Schema::create('mesin', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mesin');
            $table->string('brand_mesin');
            $table->decimal('tonase_mesin', 10, 2);
            $table->enum('status', ['aktif', 'maintenance', 'tidak aktif'])->default('aktif');
            $table->integer('maintenance_count')->default(0);
            $table->integer('offline_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin');
    }
};
