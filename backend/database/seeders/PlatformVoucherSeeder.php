<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformVoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucherIds = config('voucher_seeder.voucher_ids', []);
        foreach ($voucherIds as $id) {
            $voucherType = DB::table('vouchers')->where('id', $id)->value('voucher_type');
            if ($voucherType === 'platform') {
                DB::table('platform_vouchers')->insert([
                    'voucher_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}