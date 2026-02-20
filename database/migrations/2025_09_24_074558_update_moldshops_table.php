<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moldshops', function (Blueprint $table) {
            $table->enum('status', ['OK', 'REPAIR', 'PREVENTIVE'])
                  ->default('OK')
                  ->after('customer'); // menempatkan kolom setelah kolom customer
        });
    }

    public function down(): void
    {
        Schema::table('moldshops', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
