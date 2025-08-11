<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mesin;

echo "=== DATA MESIN ===\n";
$mesins = Mesin::all();

foreach ($mesins as $mesin) {
    echo "ID: {$mesin->id} | Nama: {$mesin->nama_mesin} | Status: {$mesin->status}\n";
}

echo "\n=== STATISTIK STATUS ===\n";
$total = $mesins->count();
$aktif = $mesins->where('status', 'aktif')->count();
$maintenance = $mesins->where('status', 'maintenance')->count();
$tidakAktif = $mesins->where('status', 'tidak_aktif')->count();

echo "Total: {$total}\n";
echo "Aktif: {$aktif}\n";
echo "Maintenance: {$maintenance}\n";
echo "Tidak Aktif: {$tidakAktif}\n"; 