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
        Schema::create('downtime_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Equipment Failure, Setup & Adjustment, etc.
            $table->string('code', 10)->unique(); // EF, SA, etc.
            $table->text('description')->nullable();
            $table->enum('type', ['planned', 'unplanned', 'both'])->default('unplanned');
            $table->string('color', 7)->default('#dc3545'); // Hex color for UI
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downtime_categories');
    }
}; 