<?php

namespace Database\Seeders;

use App\Models\Voucher;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VoucherProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách vouchers (loại product) và products
        $voucherIds = Voucher::where('voucher_type', 'product')->pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        // Kiểm tra dữ liệu
        if (empty($voucherIds) || empty($productIds)) {
            echo "Error: Please seed the vouchers and products tables first.\n";
            return;
        }

        // Tạo 20 bản ghi voucher_products
        for ($i = 0; $i < 20; $i++) {
            \App\Models\VoucherProduct::create([
                'voucher_id' => $faker->randomElement($voucherIds),
                'product_id' => $faker->randomElement($productIds),
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}