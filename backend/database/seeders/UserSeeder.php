<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buyer users
        User::create([
            'email' => 'buyer1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        User::create([
            'email' => 'buyer2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        // Seller users
        User::create([
            'email' => 'seller1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        User::create([
            'email' => 'seller2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'seller',
            'status' => 'banned',
        ]);
    }
}
