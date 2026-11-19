<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel checksheets
        Schema::create('checksheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->unsignedBigInteger('part_id');
            $table->unsignedBigInteger('user_id');
            $table->string('grup', 1)->nullable();
            $table->unsignedBigInteger('meja_id')->nullable(); // Tambahkan kolom meja_id
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
            $table->foreign('meja_id')->references('id_meja')->on('meja')->onDelete('set null'); // Foreign key untuk meja_id
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Tabel checksheet_photos (satu tabel untuk semua jenis foto)
        Schema::create('checksheet_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checksheet_id');
            $table->string('file_path');
            $table->enum('type', ['apd', 'instruksi_kerja', 'quality', 'limit_sampel', 'amptm']);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('checksheet_id')->references('id')->on('checksheets')->onDelete('cascade');
        });

        // Opsional: Mengatur ulang posisi kolom dengan ALTER TABLE
    }

    public function down(): void
    {
        Schema::dropIfExists('checksheet_photos');
        Schema::dropIfExists('checksheets');
    }
};
