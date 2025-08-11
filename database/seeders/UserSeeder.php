<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
          'username' => 'superadmin',
            'nama' => 'Super Admin',
             'email' => 'superadmin@example.com',
             'password' => Hash::make('password'),
             'role' => 'super_admin'
         ]);

         User::create([
             'username' => 'leader',
             'nama' => 'Leader',
             'email' => 'leader@example.com',
             'password' => Hash::make('password'),
             'role' => 'leader'
         ]);

         User::create([
             'username' => 'operator',
             'nama' => 'Operator',
             'email' => 'operator@example.com',
             'password' => Hash::make('password'),
             'role' => 'operator'
         ]); 

         User::create([
             'username' => 'foreman',
             'nama' => 'Foreman',
             'email' => 'foreman@example.com',
             'password' => Hash::make('password'),
             'role' => 'foreman'
         ]);

         User::create([
            'username' => 'kabag',
         'nama' => 'Kabag',
             'email' => 'kabag@example.com', 
             'password' => Hash::make('password'),
             'role' => 'kabag'
         ]);

         User::create([
             'username' => 'staff',
             'nama' => 'Staff',
             'email' => 'staff@example.com',
             'password' => Hash::make('password'),
             'role' => 'staff'
         ]);

         User::create([
             'username' => 'manager_plant',
             'nama' => 'Manager Plant',
             'email' => 'manager_plant@example.com',
             'password' => Hash::make('password'),
             'role' => 'manager_plant'
         ]);

         User::create([
             'username' => 'leader1',
            'nama' => 'Leader 1',
             'email' => 'leader1@example.com',
             'password' => Hash::make('password'),
             'role' => 'leader',
             'bagian' => 'assy'
         ]);
    }
    
} 