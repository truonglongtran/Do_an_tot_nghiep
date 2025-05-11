<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShopSeeder extends Seeder
{
    public function run()
    {
        DB::table('shops')->insert([
            [
                'owner_id' => 3,
                'shop_name' => 'Seller One Shop',
                'pickup_address' => '123 Pickup Street',
                'ward' => 'Ward 1',
                'district' => 'District A',
                'city' => 'City X',
                'phone_number' => '0900000001',
                'is_verified' => true,
                'status' => 'active',
                'avatar_url' => 'https://example.com/avatar1.jpg',
                'cover_image_url' => 'https://example.com/cover1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 4,
                'shop_name' => 'Seller Two Shop',
                'pickup_address' => '456 Pickup Road',
                'ward' => 'Ward 2',
                'district' => 'District B',
                'city' => 'City Y',
                'phone_number' => '0900000002',
                'is_verified' => false,
                'status' => 'pending',
                'avatar_url' => 'https://example.com/avatar2.jpg',
                'cover_image_url' => 'https://example.com/cover2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
