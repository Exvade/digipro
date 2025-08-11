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
        Schema::create('downtime_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesin_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Operator yang mencatat
            $table->unsignedBigInteger('oee_data_id')->nullable(); // Link ke data OEE
            $table->date('tanggal');
            $table->enum('shift', ['shift_1', 'shift_2', 'shift_3']);
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Durasi dalam menit
            
            // Downtime Classification
            $table->unsignedBigInteger('downtime_category_id');
            $table->unsignedBigInteger('downtime_code_id');
            $table->enum('downtime_type', ['planned', 'unplanned']);
            $table->enum('responsibility', ['operator', 'maintenance', 'supervisor', 'external']);
            
            // Description
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            
            // Status
            $table->enum('status', ['active', 'resolved', 'pending'])->default('active');
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('mesin_id')->references('id')->on('mesin')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('oee_data_id')->references('id')->on('oee_data')->onDelete('set null');
            $table->foreign('downtime_category_id')->references('id')->on('downtime_categories')->onDelete('cascade');
            $table->foreign('downtime_code_id')->references('id')->on('downtime_codes')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['mesin_id', 'tanggal']);
            $table->index(['downtime_type']);
            $table->index(['status']);
            $table->index(['start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downtime_events');
    }
}; 