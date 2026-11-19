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
        // Migration ini lebih lama daripada migration pembuat tabel checksheets.
        // Pada instalasi baru tabel belum ada, sehingga penghapusan harus dilewati.
        if (Schema::hasTable('checksheets') && Schema::hasColumn('checksheets', 'shift')) {
            Schema::table('checksheets', function (Blueprint $table) {
                $table->dropColumn('shift');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('checksheets') && !Schema::hasColumn('checksheets', 'shift')) {
            Schema::table('checksheets', function (Blueprint $table) {
                $table->string('shift')->nullable()->after('grup_id');
            });
        }
    }
};

