<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'superadmin', 'nik' => 'DEMO-001', 'nama' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => 'super_admin'],
            ['username' => 'leader', 'nik' => 'DEMO-002', 'nama' => 'Leader', 'email' => 'leader@example.com', 'role' => 'leader'],
            ['username' => 'operator', 'nik' => 'DEMO-003', 'nama' => 'Operator', 'email' => 'operator@example.com', 'role' => 'operator'],
            ['username' => 'foreman', 'nik' => 'DEMO-004', 'nama' => 'Foreman', 'email' => 'foreman@example.com', 'role' => 'foreman'],
            ['username' => 'kabag', 'nik' => 'DEMO-005', 'nama' => 'Kabag', 'email' => 'kabag@example.com', 'role' => 'kabag'],
            ['username' => 'staff', 'nik' => 'DEMO-006', 'nama' => 'Staff', 'email' => 'staff@example.com', 'role' => 'staff'],
            ['username' => 'manager_plant', 'nik' => 'DEMO-007', 'nama' => 'Manager Plant', 'email' => 'manager_plant@example.com', 'role' => 'manager_plant'],
            ['username' => 'leader1', 'nik' => 'DEMO-008', 'nama' => 'Leader 1', 'email' => 'leader1@example.com', 'role' => 'leader', 'bagian' => 'assy'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['username' => $user['username']],
                array_merge($user, [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ])
            );
        }
    }
}
