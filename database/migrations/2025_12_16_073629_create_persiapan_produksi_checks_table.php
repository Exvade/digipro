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
        Schema::dropIfExists('persiapan_produksi_checks');

        Schema::create('persiapan_produksi_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ploting_id')->constrained('ploting_produksi')->onDelete('cascade');
            $table->string('item_name');
            $table->boolean('operator_checked')->default(false);
            $table->boolean('leader_checked')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persiapan_produksi_checks');
    }
};
