<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucherIds = [];

        // Create 2 fixed shipping vouchers
        for ($i = 1; $i <= 2; $i++) {
            $id = DB::table('vouchers')->insertGetId([
                'code' => strtoupper('SHIP' . Str::random(6)),
                'discount_type' => ['percentage', 'fixed'][rand(0, 1)],
                'discount_value' => rand(5, 50),
                'min_order_amount' => rand(100, 500),
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(30),
                'usage_limit' => rand(10, 100),
                'used_count' => 0,
                'voucher_type' => 'shipping',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voucherIds[] = $id;
        }

        // Create 8 additional vouchers with random types (excluding shipping)
        $otherVoucherTypes = ['platform', 'shop', 'product'];
        for ($i = 1; $i <= 8; $i++) {
            $id = DB::table('vouchers')->insertGetId([
                'code' => strtoupper(Str::random(8)),
                'discount_type' => ['percentage', 'fixed'][rand(0, 1)],
                'discount_value' => rand(5, 50),
                'min_order_amount' => rand(100, 500),
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(30),
                'usage_limit' => rand(10, 100),
                'used_count' => 0,
                'voucher_type' => $otherVoucherTypes[array_rand($otherVoucherTypes)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voucherIds[] = $id;
        }

        // Store voucher IDs for other seeders
        config(['voucher_seeder.voucher_ids' => $voucherIds]);
    }
}