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
            $table->foreignId('mesin_id')->constrained('mesin')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('oee_data_id')->nullable(); // Assuming oee_data table might not exist or is optional
            $table->date('tanggal');
            $table->string('shift')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(0);

            // Foreign keys for categories/codes - nullable for now as tables might not exist
            $table->unsignedBigInteger('downtime_category_id')->nullable();
            $table->unsignedBigInteger('downtime_code_id')->nullable();

            $table->string('downtime_type')->default('offline'); // maintenance, offline, setup, setdown, etc.
            $table->string('responsibility')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('status')->default('active'); // active, resolved, pending
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
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
