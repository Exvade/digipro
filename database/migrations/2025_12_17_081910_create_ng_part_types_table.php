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
    public function up(): void
    {
        Schema::create('ng_part_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama jenis NG part');
            $table->string('code')->unique()->nullable()->comment('Kode NG part');
            $table->text('description')->nullable()->comment('Deskripsi NG part');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->integer('sort_order')->default(0)->comment('Urutan tampil');
            $table->timestamps();
            
            $table->index('is_active');
        });
        
        // Insert default NG part types berdasarkan form NG Part
        $ngParts = [
            ['name' => 'Short Mold', 'code' => 'SM', 'sort_order' => 1],
            ['name' => 'Silver', 'code' => 'SV', 'sort_order' => 2],
            ['name' => 'Weldline', 'code' => 'WL', 'sort_order' => 3],
            ['name' => 'Hairline', 'code' => 'HL', 'sort_order' => 4],
            ['name' => 'Burry/Flash', 'code' => 'BF', 'sort_order' => 5],
            ['name' => 'Crack', 'code' => 'CR', 'sort_order' => 6],
            ['name' => 'Dirty', 'code' => 'DT', 'sort_order' => 7],
            ['name' => 'Overcut', 'code' => 'OC', 'sort_order' => 8],
            ['name' => 'Yellow wish', 'code' => 'YW', 'sort_order' => 9],
            ['name' => 'Shining', 'code' => 'SH', 'sort_order' => 10],
            ['name' => 'Scrath', 'code' => 'SC', 'sort_order' => 11],
            ['name' => 'Blackdot', 'code' => 'BD', 'sort_order' => 12],
            ['name' => 'Flowmark', 'code' => 'FM', 'sort_order' => 13],
            ['name' => 'Gasmark/Airtrap', 'code' => 'GA', 'sort_order' => 14],
            ['name' => 'Sinkmark', 'code' => 'SK', 'sort_order' => 15],
            ['name' => 'Bending', 'code' => 'BG', 'sort_order' => 16],
            ['name' => 'Bubble', 'code' => 'BB', 'sort_order' => 17],
            ['name' => 'Kontaminasi', 'code' => 'KN', 'sort_order' => 18],
            ['name' => 'Ejector mark', 'code' => 'EM', 'sort_order' => 19],
            ['name' => 'Whiteness', 'code' => 'WH', 'sort_order' => 20],
            ['name' => 'Discolour', 'code' => 'DC', 'sort_order' => 21],
        ];
        
        foreach ($ngParts as $ngPart) {
            DB::table('ng_part_types')->insert([
                'name' => $ngPart['name'],
                'code' => $ngPart['code'],
                'is_active' => true,
                'sort_order' => $ngPart['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ng_part_types');
    }
};
