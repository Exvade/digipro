<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grup;

class GrupSeeder extends Seeder
{
    public function run()
    {
        // Inject Section Grups
        Grup::create([
            'nama_grup' => 'Grup A'
        ]);
        
        Grup::create([
            'nama_grup' => 'Grup B'
        ]);
        
        Grup::create([
            'nama_grup' => 'Grup C'
        ]);
        
    }
} 