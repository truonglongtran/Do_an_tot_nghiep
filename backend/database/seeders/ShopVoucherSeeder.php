<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopVoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucherIds = config('voucher_seeder.voucher_ids', []);
        foreach ($voucherIds as $id) {
            $voucherType = DB::table('vouchers')->where('id', $id)->value('voucher_type');
            if ($voucherType === 'shop') {
                DB::table('shop_vouchers')->insert([
                    'voucher_id' => $id,
                    'shop_id' => rand(1, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}