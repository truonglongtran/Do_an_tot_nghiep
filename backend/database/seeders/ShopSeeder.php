<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shops')->insert([
            [
                'owner_id' => 3, // Giả sử User ID 3 là chủ sở hữu
                'shop_name' => 'Cửa hàng A',
                'status' => 'active',
                'enabled_shipping_partners' => json_encode([1, 2]), // Shop dùng GHN (ID 1) và Viettel Post (ID 2)
                'avatar_url' => 'https://example.com/avatar_shop_a.jpg',
                'cover_image_url' => 'https://example.com/cover_shop_a.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 4, // Giả sử User ID 4 là chủ sở hữu
                'shop_name' => 'Thế giới B',
                'status' => 'active',
                'enabled_shipping_partners' => json_encode([2, 3]), // Shop dùng Viettel Post (ID 2) và Ninja Van (ID 3)
                'avatar_url' => 'https://example.com/avatar_shop_b.jpg',
                'cover_image_url' => 'https://example.com/cover_shop_b.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

