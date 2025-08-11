<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Grup;
use App\Models\Checksheet;
use App\Models\Mesin;
use App\Models\Part;

echo "=== Testing Group Filter ===\n\n";

// Test 1: Check groups
echo "1. Available Groups:\n";
$grups = Grup::all();
foreach ($grups as $grup) {
    echo "- ID: {$grup->id}, Name: {$grup->nama_grup}\n";
}

echo "\n2. Users with Groups:\n";
$users = User::with('grup')->get();
foreach ($users as $user) {
    $groupName = $user->grup ? $user->grup->nama_grup : 'No Group';
    echo "- User: {$user->nama} (ID: {$user->id}), Group: {$groupName} (Group ID: {$user->grup_id})\n";
}

echo "\n3. Total Checksheets:\n";
$totalChecksheets = Checksheet::count();
echo "Total checksheets in database: {$totalChecksheets}\n";

if ($totalChecksheets == 0) {
    echo "\n4. Creating test checksheets...\n";
    
    // Get first mesin and part
    $mesin = Mesin::first();
    $part = Part::first();
    $userWithGroup = User::whereNotNull('grup_id')->first();
    
    if ($mesin && $part && $userWithGroup) {
        // Create test checksheet for user with group
        Checksheet::create([
            'user_id' => $userWithGroup->id,
            'mesin_id' => $mesin->id,
            'part_id' => $part->id,
            'shift' => 'pagi',
            'approval_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created test checksheet for user: {$userWithGroup->nama} (Group: {$userWithGroup->grup->nama_grup})\n";
    } else {
        echo "Cannot create test data - missing mesin, part, or user with group\n";
    }
}

echo "\n5. Checksheets with Users and Groups:\n";
$checksheets = Checksheet::with(['user.grup'])->limit(5)->get();
foreach ($checksheets as $checksheet) {
    $userName = $checksheet->user ? $checksheet->user->nama : 'No User';
    $groupName = $checksheet->user && $checksheet->user->grup ? $checksheet->user->grup->nama_grup : 'No Group';
    echo "- Checksheet ID: {$checksheet->id}, User: {$userName}, Group: {$groupName}\n";
}

echo "\n6. Test Group Filter (Group ID 1):\n";
$filteredChecksheets = Checksheet::with(['user.grup'])
    ->whereHas('user', function($q) {
        $q->where('grup_id', 1);
    })
    ->get();

echo "Found " . $filteredChecksheets->count() . " checksheets for group ID 1\n";
foreach ($filteredChecksheets as $checksheet) {
    $userName = $checksheet->user ? $checksheet->user->nama : 'No User';
    $groupName = $checksheet->user && $checksheet->user->grup ? $checksheet->user->grup->nama_grup : 'No Group';
    echo "- Checksheet ID: {$checksheet->id}, User: {$userName}, Group: {$groupName}\n";
}

echo "\n=== Test Complete ===\n"; 