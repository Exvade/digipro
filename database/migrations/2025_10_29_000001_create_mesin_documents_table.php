<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesin_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->string('type'); // apd, qpoint, instruksi_kerja, limit_sampel, amptm
            $table->string('url'); // absolute URL or storage path URL
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            $table->unique(['mesin_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesin_documents');
    }
};


