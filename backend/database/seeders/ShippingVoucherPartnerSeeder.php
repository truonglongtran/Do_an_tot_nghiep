<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingVoucherPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $shippingVoucherIds = config('voucher_seeder.shipping_voucher_ids', []);
        foreach ($shippingVoucherIds as $shippingVoucherId) {
            foreach (range(1, rand(1, 3)) as $_) {
                DB::table('shipping_voucher_partners')->insert([
                    'shipping_voucher_id' => $shippingVoucherId,
                    'shipping_partner_id' => rand(1, 3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}