<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách orders và products
        $orders = Order::select('id', 'buyer_id')->get();
        $productIds = Product::pluck('id')->toArray();

        // Kiểm tra dữ liệu
        if ($orders->isEmpty() || empty($productIds)) {
            echo "Error: Please seed the orders and products tables first.\n";
            return;
        }

        // Tạo 30 bản ghi reviews
        for ($i = 0; $i < 30; $i++) {
            $order = $faker->randomElement($orders);
            $hasImages = $faker->boolean(30); // 30% cơ hội có hình ảnh

            \App\Models\Review::create([
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'product_id' => $faker->randomElement($productIds),
                'rating' => $faker->numberBetween(1, 5),
                'comment' => $faker->optional()->paragraph(),
                'images' => $hasImages ? json_encode([$faker->imageUrl(), $faker->imageUrl()]) : null,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}