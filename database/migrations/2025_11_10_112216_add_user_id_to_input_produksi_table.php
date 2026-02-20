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
        Schema::table('input_produksi', function (Blueprint $table) {
            // Tambahkan kolom user_id untuk menyimpan siapa yang approve/reject
            if (!Schema::hasColumn('input_produksi', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('ploting_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('input_produksi', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
