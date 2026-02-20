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
            $table->text('operator_signature_qr')->nullable()->after('approved_at');
            $table->text('leader_signature_qr')->nullable()->after('operator_signature_qr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            $table->dropColumn(['operator_signature_qr', 'leader_signature_qr']);
        });
    }
};
