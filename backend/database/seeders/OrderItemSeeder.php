<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách ID của orders và products
        $orderIds = Order::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        // Kiểm tra xem có orders và products hay không
        if (empty($orderIds) || empty($productIds)) {
            echo "Error: Please seed the orders and products tables first.\n";
            return;
        }

        // Tạo 50 bản ghi order_items
        for ($i = 0; $i < 50; $i++) {
            $quantity = $faker->numberBetween(1, 5); // Số lượng từ 1 đến 5
            $price = $faker->randomFloat(2, 5, 500); // Giá từ 5.00 đến 500.00

            \App\Models\OrderItem::create([
                'order_id' => $faker->randomElement($orderIds),
                'product_id' => $faker->randomElement($productIds),
                'quantity' => $quantity,
                'price' => $price,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}