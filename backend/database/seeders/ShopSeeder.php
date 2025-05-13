<?php

// database/seeders/ShopSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;

class ShopSeeder extends Seeder
{
    public function run()
    {
        Shop::insert([
            [
                'owner_id' => 3,
                'shop_name' => 'Seller One Shop',
                'status' => 'active',
                'avatar_url' => 'https://example.com/avatar1.jpg',
                'cover_image_url' => 'https://example.com/cover1.jpg',
                'pickup_address' => '123 Pickup Street',
                'ward' => 'Ward 1',
                'district' => 'District A',
                'city' => 'City X',
                'phone_number' => '0900000001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 4,
                'shop_name' => 'Seller Two Shop',
                'status' => 'pending',
                'avatar_url' => 'https://example.com/avatar2.jpg',
                'cover_image_url' => 'https://example.com/cover2.jpg',
                'pickup_address' => '456 Pickup Road',
                'ward' => 'Ward 2',
                'district' => 'District B',
                'city' => 'City Y',
                'phone_number' => '0900000002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
