<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\PlanningProduksi;
use App\Models\InputProduksi;
use Carbon\Carbon;

$today = Carbon::today()->toDateString();

echo "--- Status Summary (Overall) ---\n";
$statuses = InputProduksi::select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
foreach ($statuses as $s) {
    echo "Status: " . ($s->status ?: 'NULL') . " | Count: " . $s->count . "\n";
}

echo "\n--- Approved Planning Distribution ---\n";
$allApproved = PlanningProduksi::whereHas('inputProduksi', function($q) {
    $q->where('status', 'approved');
})->get();

foreach ($allApproved as $p) {
    echo "Planning ID: {$p->id} | Tanggal: {$p->tanggal} | Bagian: {$p->bagian}\n";
}

echo "\nToday Date: $today\n";
