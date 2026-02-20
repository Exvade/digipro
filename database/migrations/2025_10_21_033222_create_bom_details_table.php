<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moldshop_id')
                  ->constrained('moldshops')
                  ->onDelete('cascade');
            $table->foreignId('part_id')
                  ->constrained('parts')
                  ->onDelete('cascade');
            
            $table->integer('cavity_mold')->default(1);
            $table->decimal('cycle_time_std', 10, 2)->default(0.00);
            $table->string('resin_type')->nullable();
            $table->decimal('shot_weight', 10, 2)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Optional: hindari duplikasi kombinasi mold + part
            $table->unique(['moldshop_id', 'part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_details');
    }
};
