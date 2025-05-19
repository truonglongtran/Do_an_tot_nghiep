<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingVoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucherIds = config('voucher_seeder.voucher_ids', []);
        $shippingVoucherIds = [];

        foreach ($voucherIds as $id) {
            $voucherType = DB::table('vouchers')->where('id', $id)->value('voucher_type');
            if ($voucherType === 'shipping') {
                $shippingVoucherId = DB::table('shipping_vouchers')->insertGetId([
                    'voucher_id' => $id,
                    'shipping_only' => rand(0, 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $shippingVoucherIds[] = $shippingVoucherId;
            }
        }

        config(['voucher_seeder.shipping_voucher_ids' => $shippingVoucherIds]);
    }
}