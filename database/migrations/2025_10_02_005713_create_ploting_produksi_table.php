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
        Schema::create('ploting_produksi', function (Blueprint $table) {
            $table->id();

            // Relasi ke planning produksi
            $table->foreignId('planning_id')
                  ->constrained('planning_produksi')
                  ->onDelete('cascade');

            // Bisa assign ke grup
            $table->foreignId('grup_id')
                  ->nullable()
                  ->constrained('grups')
                  ->onDelete('set null');

            // Bisa assign langsung ke operator
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->enum('status', ['waiting', 'on_progress', 'done'])->default('waiting');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ploting_produksi');
    }
};