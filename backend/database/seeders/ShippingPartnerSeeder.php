<?php
// database/seeders/ShippingPartnerSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingPartner;

class ShippingPartnerSeeder extends Seeder
{
    public function run()
    {
        ShippingPartner::insert([
            ['name' => 'ghn', 'api_url' => 'https://api.ghn.vn', 'status' => 'active'],
            ['name' => 'giao_hang_tiet_kiem', 'api_url' => 'https://api.ghtk.vn', 'status' => 'active'],
            ['name' => 'viettel_post', 'api_url' => 'https://api.viettel.vn', 'status' => 'active'],
        ]);
    }
}
