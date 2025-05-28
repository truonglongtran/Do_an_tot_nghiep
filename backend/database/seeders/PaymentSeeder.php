<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Lấy tất cả order_id từ bảng orders
        $orderIds = DB::table('orders')->pluck('id')->toArray();

        if (empty($orderIds)) {
            \Illuminate\Support\Facades\Log::warning('No orders found. Please run OrderSeeder first.');
            return;
        }

        $payments = [];
        foreach ($orderIds as $orderId) {
            $payments[] = [
                'order_id' => $orderId,
                'amount' => $faker->randomFloat(2, 100000, 10000000), // Số tiền từ 100,000 đến 10,000,000 VND
                'payment_method' => $faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'mobile_payment']),
                'status' => $faker->randomElement(['success', 'failed', 'refund']),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => now(),
            ];
        }

        DB::table('payments')->insert($payments);
    }
}