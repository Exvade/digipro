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
        Schema::create('oee_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->decimal('target_oee', 5, 2); // Target OEE persentase
            $table->decimal('target_availability', 5, 2); // Target Availability persentase
            $table->decimal('target_performance', 5, 2); // Target Performance persentase
            $table->decimal('target_quality', 5, 2); // Target Quality persentase
            
            // Time targets
            $table->integer('target_cycle_time'); // Target cycle time dalam detik
            $table->integer('max_downtime_per_day'); // Maksimal downtime per hari dalam menit
            $table->integer('max_stops_per_day'); // Maksimal stop per hari
            
            // Period
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'yearly'])->default('daily');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            
            // Indexes
            $table->index(['mesin_id', 'is_active']);
            $table->index(['effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oee_targets');
    }
}; 