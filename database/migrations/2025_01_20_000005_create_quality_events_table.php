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
        Schema::create('quality_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Operator yang mencatat
            $table->unsignedBigInteger('oee_data_id')->nullable(); // Link ke data OEE
            $table->date('tanggal');
            $table->enum('shift', ['shift_1', 'shift_2', 'shift_3']);
            $table->time('time_occurred');
            
            // Quality Classification
            $table->enum('defect_type', ['visual', 'dimensional', 'functional', 'material', 'other']);
            $table->string('defect_code', 20);
            $table->string('defect_description');
            $table->integer('quantity_defective');
            $table->integer('quantity_checked');
            
            // Root Cause
            $table->enum('root_cause_category', ['machine', 'material', 'method', 'man', 'environment']);
            $table->text('root_cause_description')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            
            // Cost Impact
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            
            // Status
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('oee_data_id')->references('id')->on('oee_data')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['mesin_id', 'tanggal']);
            $table->index(['defect_type']);
            $table->index(['status']);
            $table->index(['time_occurred']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_events');
    }
}; 