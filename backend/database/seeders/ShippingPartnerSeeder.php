<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shipping_partners')->insert([
            [
                'name' => 'Giao hàng nhanh',
                'api_url' => 'https://ghn.vn/api',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Viettel Post',
                'api_url' => 'https://viettelpost.vn/api',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ninja Van',
                'api_url' => 'https://ninjavan.vn/api',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'J&T Express',
                'api_url' => 'https://jtexpress.vn/api',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VNPost Express',
                'api_url' => 'https://vnpost.vn/api',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}