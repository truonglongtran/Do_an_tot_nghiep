<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingPartner;

class ShippingPartnerSeeder extends Seeder
{
    public function run()
    {
        ShippingPartner::insert([
            ['name' => 'Giao Hàng Nhanh', 'api_url' => 'https://api.ghn.vn', 'status' => 'active'],
            ['name' => 'Giao Hàng Tiết Kiệm', 'api_url' => 'https://api.ghtk.vn', 'status' => 'active'],
            ['name' => 'Viettel Post', 'api_url' => 'https://api.viettelpost.vn', 'status' => 'active'],
            ['name' => 'VNPost', 'api_url' => 'https://api.vnpost.vn', 'status' => 'inactive'],
            ['name' => 'Ahamove', 'api_url' => 'https://api.ahamove.com', 'status' => 'active'],
            ['name' => 'Lalamove', 'api_url' => 'https://rest.lalamove.com', 'status' => 'active'],
            ['name' => 'Ninja Van', 'api_url' => 'https://api.ninjavan.co', 'status' => 'inactive'],
            ['name' => 'J&T Express', 'api_url' => 'https://api.jtexpress.vn', 'status' => 'active'],
            ['name' => 'BEST Express', 'api_url' => 'https://api.best-inc.vn', 'status' => 'active'],
            ['name' => 'GrabExpress', 'api_url' => 'https://api.grab.com', 'status' => 'active'],
        ]);
    }
}
