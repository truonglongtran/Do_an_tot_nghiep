<?php
// database/seeders/ShopShippingPartnerSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\ShippingPartner;

class ShopShippingPartnerSeeder extends Seeder
{
    public function run()
    {
        $shop1 = Shop::find(1); // Ví dụ, lấy shop có ID = 3
        $shop2 = Shop::find(2); // Ví dụ, lấy shop có ID = 4

        // Gắn Shipping Partners cho Shop 1
        $shop1->shippingPartners()->attach([1, 2]); // ghb và ghtk

        // Gắn Shipping Partners cho Shop 2
        $shop2->shippingPartners()->attach([3]); // viettel_post
    }
}
