<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyerAddressSeeder extends Seeder
{
    public function run()
    {
        // Dữ liệu cho bảng buyer_addresses (Buyer và Seller có thể có nhiều địa chỉ)
        DB::table('buyer_addresses')->insert([
            // Buyer 1
            [
                'user_id' => 1,  // Buyer 1
                'recipient_name' => 'John Doe',
                'phone_number' => '0123456789',
                'address_line' => '123 Buyer Street',
                'ward' => 'Ward 1',
                'district' => 'District A',
                'city' => 'City X',
                'is_default' => true,  // Địa chỉ mặc định
            ],
            [
                'user_id' => 1,  // Buyer 1
                'recipient_name' => 'John Doe',
                'phone_number' => '0123456789',
                'address_line' => '456 Another Road',
                'ward' => 'Ward 2',
                'district' => 'District B',
                'city' => 'City Y',
                'is_default' => false,
            ],
            // Buyer 2
            [
                'user_id' => 2,  // Buyer 2
                'recipient_name' => 'Jane Doe',
                'phone_number' => '0987654321',
                'address_line' => '789 Buyer Avenue',
                'ward' => 'Ward 3',
                'district' => 'District C',
                'city' => 'City Z',
                'is_default' => true,  // Địa chỉ mặc định
            ],
            [
                'user_id' => 2,  // Buyer 2
                'recipient_name' => 'Jane Doe',
                'phone_number' => '0987654321',
                'address_line' => '101 Another Street',
                'ward' => 'Ward 4',
                'district' => 'District D',
                'city' => 'City W',
                'is_default' => false,
            ],
            // Seller 1 (Cũng là Buyer)
            [
                'user_id' => 3,  // Seller 1 (cũng là buyer)
                'recipient_name' => 'Seller One',
                'phone_number' => '0912345678',
                'address_line' => '123 Seller Street',
                'ward' => 'Ward 5',
                'district' => 'District E',
                'city' => 'City Y',
                'is_default' => true,  // Địa chỉ mặc định
            ],
            [
                'user_id' => 3,  // Seller 1 (cũng là buyer)
                'recipient_name' => 'Seller One',
                'phone_number' => '0912345678',
                'address_line' => '456 Pickup Road',
                'ward' => 'Ward 6',
                'district' => 'District F',
                'city' => 'City Z',
                'is_default' => false,
            ],
            // Seller 2 (Cũng là Buyer)
            [
                'user_id' => 4,  // Seller 2 (cũng là buyer)
                'recipient_name' => 'Seller Two',
                'phone_number' => '0923456789',
                'address_line' => '789 Seller Avenue',
                'ward' => 'Ward 7',
                'district' => 'District G',
                'city' => 'City W',
                'is_default' => true,  // Địa chỉ mặc định
            ],
            [
                'user_id' => 4,  // Seller 2 (cũng là buyer)
                'recipient_name' => 'Seller Two',
                'phone_number' => '0923456789',
                'address_line' => '101 Pickup Street',
                'ward' => 'Ward 8',
                'district' => 'District H',
                'city' => 'City V',
                'is_default' => false,
            ],
        ]);
    }
}
