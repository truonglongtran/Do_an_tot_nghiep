<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách ID của buyers và sellers từ bảng users
        $buyers = User::where('role', 'buyer')->pluck('id')->toArray();
        $sellers = User::where('role', 'seller')->pluck('id')->toArray();

        // Kiểm tra xem có buyers và sellers hay không
        if (empty($buyers) || empty($sellers)) {
            echo "Error: Please seed the users table with buyers and sellers first.\n";
            return;
        }

        // Tạo 50 bản ghi orders
        for ($i = 0; $i < 50; $i++) {
            $orderStatus = $faker->randomElement(['pending', 'paid', 'canceled']);
            $settledStatus = 'unsettled';
            $settledAt = null;
            $shippingStatus = $faker->randomElement(['pending', 'processing', 'shipping', 'delivered', 'failed', 'return']);

            // Logic cho settled_status và settled_at
            if ($orderStatus === 'paid' && $faker->boolean(70)) { // 70% cơ hội settled nếu đã paid
                $settledStatus = 'settled';
                $settledAt = $faker->dateTimeBetween('-1 month', 'now');
            }

            Order::create([
                'buyer_id' => $faker->randomElement($buyers),
                'seller_id' => $faker->randomElement($sellers),
                'total_amount' => $faker->randomFloat(2, 10, 1000), // Giá trị từ 10.00 đến 1000.00
                'settled_status' => $settledStatus,
                'settled_at' => $settledAt,
                'shipping_status' => $shippingStatus,
                'order_status' => $orderStatus,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}