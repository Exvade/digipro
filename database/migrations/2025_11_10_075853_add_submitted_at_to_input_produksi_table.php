<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('input_produksi', function (Blueprint $table) {
        $table->timestamp('submitted_at')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('input_produksi', function (Blueprint $table) {
        $table->dropColumn('submitted_at');
    });
}
};
