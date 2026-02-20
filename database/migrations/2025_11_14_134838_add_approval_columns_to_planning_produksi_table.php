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
        Schema::table('planning_produksi', function (Blueprint $table) {
            // Status approval: pending, approved, rejected
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('status');
            
            // User yang approve (kabag PPIC)
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->after('status_approval');
            
            // Tanggal approval
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Alasan rejection (jika ditolak)
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'status_approval',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
