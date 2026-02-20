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
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->string('signature_qr_code')->nullable()->after('catatan');
            $table->string('signed_by')->nullable()->after('signature_qr_code');
            $table->timestamp('signed_at')->nullable()->after('signed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->dropColumn(['signature_qr_code', 'signed_by', 'signed_at']);
        });
    }
};
