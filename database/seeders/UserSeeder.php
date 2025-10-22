<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Admin
        User::firstOrCreate(
            ['email' => 'admin@kopi.kata'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('admin123'),
                'role' => 'admin'
            ]
        );

        // 2. Data User
        User::firstOrCreate(
            ['email' => 'raka@gmail.com'],
            [
                'name' => 'raka',
                'password' => bcrypt('raka12345')
            ]
        );
    }
}