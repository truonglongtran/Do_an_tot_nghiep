<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        Admin::create([
            'email' => 'admin1@example.com',
            'password' => Hash::make('adminpass1'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        Admin::create([
            'email' => 'admin2@example.com',
            'password' => Hash::make('adminpass2'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        Admin::create([
            'email' => 'moderator1@example.com',
            'password' => Hash::make('modpass1'),
            'role' => 'moderator',
            'status' => 'active',
        ]);

        Admin::create([
            'email' => 'moderator2@example.com',
            'password' => Hash::make('modpass2'),
            'role' => 'moderator',
            'status' => 'active',
        ]);
    }
}
