<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'status' => 'active',
            'role' => 'admin',
            'password' => Hash::make('admin')
        ]);

        User::create([
            'name' => 'staff',
            'email' => 'staff@gmail.com',
            'status' => 'active',
            'role' => 'staff',
            'password' => Hash::make('staff')
        ]);
    }
}
