<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moldshops', function (Blueprint $table) {
            $table->id();
            $table->string('no_mold')->unique();   // Nomor mold, unik
            $table->string('nama_mold');           // Nama mold
            $table->string('type');                // Type mold
            $table->string('customer');            // Nama customer
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moldshops');
    }
};
