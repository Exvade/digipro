<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            // Relasi ke jadwal kerja
            $table->foreignId('jadwal_kerja_id')
                  ->nullable()
                  ->constrained('jadwal_kerjas')
                  ->onDelete('set null')
                  ->after('part_id');

            // Informasi Shift
            $table->enum('shift', ['pagi', 'siang', 'malam'])->after('qty_planning_produksi');
            
            // SHIFT PAGI (07:00 - 15:00)
            $table->integer('pagi_jam_07')->default(0)->after('shift');
            $table->integer('pagi_jam_08')->default(0);
            $table->integer('pagi_jam_09')->default(0);
            $table->integer('pagi_jam_10')->default(0);
            $table->integer('pagi_jam_11')->default(0);
            $table->integer('pagi_jam_12')->default(0);
            $table->integer('pagi_jam_13')->default(0);
            $table->integer('pagi_jam_14')->default(0);

            // SHIFT SIANG (15:00 - 23:00)
            $table->integer('siang_jam_15')->default(0);
            $table->integer('siang_jam_16')->default(0);
            $table->integer('siang_jam_17')->default(0);
            $table->integer('siang_jam_18')->default(0);
            $table->integer('siang_jam_19')->default(0);
            $table->integer('siang_jam_20')->default(0);
            $table->integer('siang_jam_21')->default(0);
            $table->integer('siang_jam_22')->default(0);

            // SHIFT MALAM (23:00 - 07:00)
            $table->integer('malam_jam_23')->default(0);
            $table->integer('malam_jam_00')->default(0);
            $table->integer('malam_jam_01')->default(0);
            $table->integer('malam_jam_02')->default(0);
            $table->integer('malam_jam_03')->default(0);
            $table->integer('malam_jam_04')->default(0);
            $table->integer('malam_jam_05')->default(0);
            $table->integer('malam_jam_06')->default(0);

            // Target per Shift
            $table->integer('target_shift_pagi')->default(0);
            $table->integer('target_shift_siang')->default(0);
            $table->integer('target_shift_malam')->default(0);

            // Total semua shift
            $table->integer('total_shift')->default(0);

            // Status
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled'])
                  ->default('draft');
            $table->text('catatan')->nullable();

            // Index
            $table->index(['tanggal', 'shift']);
            $table->index(['moldshop_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('planning_produksi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jadwal_kerja_id');
            $table->dropIndex(['tanggal', 'shift']);
            $table->dropIndex(['moldshop_id', 'tanggal']);

            $table->dropColumn([
                'shift',
                // shift pagi
                'pagi_jam_07','pagi_jam_08','pagi_jam_09','pagi_jam_10',
                'pagi_jam_11','pagi_jam_12','pagi_jam_13','pagi_jam_14',
                // shift siang
                'siang_jam_15','siang_jam_16','siang_jam_17','siang_jam_18',
                'siang_jam_19','siang_jam_20','siang_jam_21','siang_jam_22',
                // shift malam
                'malam_jam_23','malam_jam_00','malam_jam_01','malam_jam_02',
                'malam_jam_03','malam_jam_04','malam_jam_05','malam_jam_06',
                // target
                'target_shift_pagi','target_shift_siang','target_shift_malam',
                'total_shift','status','catatan'
            ]);
        });
    }
};
