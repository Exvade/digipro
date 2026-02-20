<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            $table->unsignedBigInteger('ploting_id')->after('planning_id')->nullable();
            $table->foreign('ploting_id')->references('id')->on('ploting_produksi')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('input_produksi', function (Blueprint $table) {
            $table->dropForeign(['ploting_id']);
            $table->dropColumn('ploting_id');
        });
    }
};
