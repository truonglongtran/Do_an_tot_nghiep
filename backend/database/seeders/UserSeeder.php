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
    'username' => 'buyer1',
    'email' => 'buyer1@example.com',
    'phone_number' => '0901234567',
    'avatar_url' => 'https://example.com/avatars/buyer1.jpg',
    'password' => Hash::make('password123'),
    'role' => 'buyer',
    'status' => 'active',
]);

User::create([
    'username' => 'buyer2',
    'email' => 'buyer2@example.com',
    'phone_number' => '0901234568',
    'avatar_url' => 'https://example.com/avatars/buyer2.jpg',
    'password' => Hash::make('password123'),
    'role' => 'buyer',
    'status' => 'active',
]);

// Seller users
User::create([
    'username' => 'seller1',
    'email' => 'seller1@example.com',
    'phone_number' => '0901234569',
    'avatar_url' => 'https://example.com/avatars/seller1.jpg',
    'password' => Hash::make('password123'),
    'role' => 'seller',
    'status' => 'active',
]);

User::create([
    'username' => 'seller2',
    'email' => 'seller2@example.com',
    'phone_number' => '0901234570',
    'avatar_url' => 'https://example.com/avatars/seller2.jpg',
    'password' => Hash::make('password123'),
    'role' => 'seller',
    'status' => 'banned',
]);

    }
}