<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách orders cùng với total_amount
        $orders = Order::select('id', 'total_amount')->get();

        // Kiểm tra xem có orders hay không
        if ($orders->isEmpty()) {
            echo "Error: Please seed the orders table first.\n";
            return;
        }

        // Tạo bản ghi payments cho mỗi order
        foreach ($orders as $order) {
            $paymentMethod = $faker->randomElement(['credit_card', 'bank_transfer', 'paypal', 'cash_on_delivery']);
            $status = $faker->randomElement(['success', 'failed', 'refund']);

            \App\Models\Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount, // Lấy từ total_amount của order
                'payment_method' => $paymentMethod,
                'status' => $status,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}