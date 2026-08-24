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
        Schema::table('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->string('bukti')->nullable()->after('aktual_tanggal');
            $table->enum('status_aktual', ['pending', 'approved', 'rejected'])->default('pending')->after('bukti');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('status_aktual');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['bukti', 'status_aktual', 'approved_by', 'approved_at']);
        });
    }
};
