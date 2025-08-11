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
        Schema::table('users', function (Blueprint $table) {
            // $table->dropColumn('grup');
            $table->foreignId('grup_id')->nullable()->after('bagian')->constrained('grups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('grup', ['A', 'B', 'C'])->nullable()->after('bagian');
            $table->dropForeign(['grup_id']);
            $table->dropColumn('grup_id');
        });
    }
};
