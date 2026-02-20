<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixUserNikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ambil user yang nik kosong/null
        $users = DB::table('users')->whereNull('nik')->orWhere('nik', '')->get();

        // mulai dari nomor dasar
        $baseNik = 321606400500;

        // cari nik terakhir di DB (kalau ada yang sudah numeric)
        $lastNik = DB::table('users')->max('nik');
        if (is_numeric($lastNik)) {
            $baseNik = max($baseNik, (int)$lastNik + 1);
        }

        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['nik' => (string) $baseNik]);

            $baseNik++; // increment untuk user berikutnya
        }
    }
}
