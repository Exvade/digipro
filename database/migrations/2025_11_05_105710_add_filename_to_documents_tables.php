<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add filename column to mesin_documents table
        if (Schema::hasTable('mesin_documents') && !Schema::hasColumn('mesin_documents', 'filename')) {
            Schema::table('mesin_documents', function (Blueprint $table) {
                $table->string('filename')->nullable()->after('url');
            });
        }

        // Add filename column to meja_documents table
        if (Schema::hasTable('meja_documents') && !Schema::hasColumn('meja_documents', 'filename')) {
            Schema::table('meja_documents', function (Blueprint $table) {
                $table->string('filename')->nullable()->after('url');
            });
        }
    }

    public function down(): void
    {
        // Remove filename column from mesin_documents table
        if (Schema::hasTable('mesin_documents') && Schema::hasColumn('mesin_documents', 'filename')) {
            Schema::table('mesin_documents', function (Blueprint $table) {
                $table->dropColumn('filename');
            });
        }

        // Remove filename column from meja_documents table
        if (Schema::hasTable('meja_documents') && Schema::hasColumn('meja_documents', 'filename')) {
            Schema::table('meja_documents', function (Blueprint $table) {
                $table->dropColumn('filename');
            });
        }
    }
};
