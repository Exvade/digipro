<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\User::where('nama', 'like', '%Hafizh%')->first();
if ($u) {
    echo "ID: " . $u->id . "\n";
    echo "Role: " . $u->role . "\n";
    echo "Dept: " . $u->departemen . "\n";
} else {
    echo "User not found\n";
}
