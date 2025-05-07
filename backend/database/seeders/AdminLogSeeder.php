<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Dispute;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AdminLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách admin, products, và disputes
        $adminIds = Admin::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $disputeIds = Dispute::pluck('id')->toArray();

        // Kiểm tra dữ liệu
        if (empty($adminIds)) {
            echo "Error: Please seed the admins table first.\n";
            return;
        }

        // Các loại hành động admin
        $actionTypes = [
            'product_approved' => $productIds,
            'product_rejected' => $productIds,
            'dispute_resolved' => $disputeIds,
            'dispute_rejected' => $disputeIds,
            'user_banned' => $adminIds, // Giả sử target_id có thể là admin hoặc user
        ];

        // Tạo 20 bản ghi admin_logs
        for ($i = 0; $i < 20; $i++) {
            $actionType = $faker->randomElement(array_keys($actionTypes));
            $targetIds = $actionTypes[$actionType];

            if (empty($targetIds)) {
                continue; // Bỏ qua nếu không có target_id phù hợp
            }

            \App\Models\AdminLog::create([
                'admin_id' => $faker->randomElement($adminIds),
                'action_type' => $actionType,
                'target_id' => $faker->randomElement($targetIds),
                'description' => $faker->optional(0.8)->sentence(),
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}