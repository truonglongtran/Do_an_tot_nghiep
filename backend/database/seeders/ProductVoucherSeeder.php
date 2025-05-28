<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucherIds = config('voucher_seeder.voucher_ids', []);
        foreach ($voucherIds as $id) {
            $voucherType = DB::table('vouchers')->where('id', $id)->value('voucher_type');
            if ($voucherType === 'product') {
                foreach (range(1, rand(2, 5)) as $_) {
                    DB::table('product_vouchers')->insert([
                        'voucher_id' => $id,
                        'product_id' => rand(1, 20),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}