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
        Schema::create('oee_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Operator yang mencatat
            $table->date('tanggal');
            $table->enum('shift', ['shift_1', 'shift_2', 'shift_3']);
            $table->time('start_time');
            $table->time('end_time');
            
            // Availability Data
            $table->integer('planned_production_time'); // Menit
            $table->integer('actual_running_time'); // Menit
            $table->integer('total_downtime'); // Menit
            $table->decimal('availability', 5, 2); // Persentase
            
            // Performance Data
            $table->integer('ideal_cycle_time'); // Detik per unit
            $table->integer('actual_cycle_time'); // Detik per unit
            $table->integer('total_units_produced');
            $table->integer('good_units_produced');
            $table->integer('rejected_units');
            $table->decimal('performance', 5, 2); // Persentase
            
            // Quality Data
            $table->decimal('quality', 5, 2); // Persentase
            
            // Overall OEE
            $table->decimal('oee', 5, 2); // Persentase
            
            // Additional Metrics
            $table->integer('stop_count');
            $table->integer('setup_time'); // Menit
            $table->integer('changeover_time'); // Menit
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['mesin_id', 'tanggal', 'shift']);
            $table->index(['tanggal']);
            $table->index(['shift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oee_data');
    }
}; 