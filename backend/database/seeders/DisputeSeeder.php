<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách orders và users (buyers, sellers)
        $orders = Order::select('id', 'buyer_id', 'seller_id')->get();
        $users = User::pluck('id')->toArray();

        // Kiểm tra dữ liệu
        if ($orders->isEmpty() || empty($users)) {
            echo "Error: Please seed the orders and users tables first.\n";
            return;
        }

        // Tạo 20 bản ghi disputes
        for ($i = 0; $i < 20; $i++) {
            $order = $faker->randomElement($orders);
            $status = $faker->randomElement(['open', 'resolved', 'rejected']);
            $adminNote = $status !== 'open' ? $faker->sentence() : null;

            \App\Models\Dispute::create([
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'reason' => $faker->paragraph(2),
                'status' => $status,
                'admin_note' => $adminNote,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}