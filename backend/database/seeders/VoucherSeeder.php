<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách shop và shipping partners
        $shopIds = Shop::pluck('id')->toArray();
        $shippingPartners = ['partner1', 'partner2', 'partner3'];

        // Tạo 15 bản ghi vouchers
        for ($i = 0; $i < 15; $i++) {
            $voucherType = $faker->randomElement(['platform', 'shop', 'shipping', 'product']);
            $shopId = $voucherType === 'shop' ? $faker->randomElement($shopIds) : null;
            $shippingOnly = $voucherType === 'shipping' ? true : false;
            $discountType = $faker->randomElement(['percentage', 'fixed']);
            $discountValue = $discountType === 'percentage' ? $faker->numberBetween(5, 50) : $faker->randomFloat(2, 5, 100);

            \App\Models\Voucher::create([
                'code' => strtoupper($faker->unique()->lexify('VOUCHER_????')),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'min_order_amount' => $faker->randomFloat(2, 10, 200),
                'start_date' => $faker->dateTimeBetween('-1 month', 'now'),
                'end_date' => $faker->dateTimeBetween('now', '+1 month'),
                'usage_limited' => $faker->optional(0.7, null)->numberBetween(50, 500),
                'used_count' => $faker->numberBetween(0, 50),
                'voucher_type' => $voucherType,
                'shop_id' => $shopId,
                'shipping_only' => $shippingOnly,
                'applicable_shipping_partners' => $shippingOnly ? json_encode($faker->randomElements($shippingPartners, 2)) : null,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}