<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meja_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meja_id');
            $table->string('type'); // generic type identifier per business need
            $table->string('url');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('meja_id')->references('id_meja')->on('meja')->onDelete('cascade');
            $table->unique(['meja_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meja_documents');
    }
};


