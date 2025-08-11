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
        Schema::create('downtime_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('downtime_category_id');
            $table->string('name'); // Specific downtime reason
            $table->string('code', 20)->unique(); // Unique code
            $table->text('description')->nullable();
            $table->enum('type', ['planned', 'unplanned'])->default('unplanned');
            $table->enum('responsibility', ['operator', 'maintenance', 'supervisor', 'external'])->default('operator');
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('downtime_category_id')->references('id')->on('downtime_categories')->onDelete('cascade');
            
            // Indexes
            $table->index(['downtime_category_id']);
            $table->index(['type']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downtime_codes');
    }
}; 