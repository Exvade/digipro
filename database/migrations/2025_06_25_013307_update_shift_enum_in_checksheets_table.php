<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Update data lama sebelum mengubah tipe kolom
        DB::table('checksheets')->where('shift', '1')->update(['shift' => 'shift_1']);
        DB::table('checksheets')->where('shift', '2')->update(['shift' => 'shift_2']);
        DB::table('checksheets')->where('shift', '3')->update(['shift' => 'shift_3']);
        // ...tambahkan mapping lain jika ada...

        Schema::table('checksheets', function (Blueprint $table) {
            $table->enum('shift', [
                'shift_1', // 07.00-15.00
                'shift_2', // 15.00-23.00
                'shift_3', // 23.00-07.00
            ])->change();
        });
    }
    
    public function down()
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->string('shift')->change();
        });
    }
};
