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
        Schema::create('produksi_per_jam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')
                ->constrained('planning_produksi')
                ->onDelete('cascade');
            $table->foreignId('input_produksi_id')
                ->nullable()
                ->constrained('input_produksi')
                ->onDelete('set null');
            $table->foreignId('ploting_id')
                ->nullable()
                ->constrained('ploting_produksi')
                ->onDelete('set null');
            $table->enum('shift', ['pagi', 'siang', 'malam']);
            $table->string('jam', 20); // Format: "07.00 - 08.00" atau "07:00"
            $table->integer('target')->default(0);
            
            // Qty Output Produksi
            $table->integer('ok_count')->default(0);
            $table->integer('ng_part')->default(0);
            
            // Kode Operator
            $table->string('operator_code', 50)->nullable();
            
            // NG Subpart (1-7)
            $table->integer('ng_subpart_1')->default(0);
            $table->integer('ng_subpart_2')->default(0);
            $table->integer('ng_subpart_3')->default(0);
            $table->integer('ng_subpart_4')->default(0);
            $table->integer('ng_subpart_5')->default(0);
            $table->integer('ng_subpart_6')->default(0);
            $table->integer('ng_subpart_7')->default(0);
            
            // MASALAH dan TINDAKAN
            $table->text('masalah')->nullable();
            $table->text('tindakan')->nullable();
            
            // Checker
            $table->string('checker_prd', 100)->nullable();
            $table->string('checker_qc', 100)->nullable();
            
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['planning_id', 'shift', 'jam']);
            $table->index(['input_produksi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_per_jam');
    }
};
