<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shop;
use App\Models\ShopFollower;
use Illuminate\Support\Facades\Log;

class ShopFollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách người dùng active (bao gồm buyer và seller)
        $activeUsers = User::where('status', 'active')->get();

        if ($activeUsers->isEmpty()) {
            Log::warning('No active users found for shop followers seeding.');
            return;
        }

        // Lấy danh sách cửa hàng active
        $activeShops = Shop::where('status', 'active')->get();

        if ($activeShops->isEmpty()) {
            Log::warning('No active shops found for shop followers seeding.');
            return;
        }

        foreach ($activeUsers as $user) {
            // Mỗi người dùng ngẫu nhiên theo dõi 0-2 cửa hàng
            $randomShops = $activeShops->random(rand(0, min(2, $activeShops->count())));

            // Xử lý trường trường trường hợp $randomShops là một model duy nhất (khi count = 1)
            if (!is_iterable($randomShops)) {
                $randomShops = [$randomShops];
            }

            foreach ($randomShops as $shop) {
                // Tránh người dùng theo dõi chính cửa hàng của họ
                if ($shop->owner_id !== $user->id) {
                    ShopFollower::create([
                        'user_id' => $user->id,
                        'shop_id' => $shop->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Created shop follower', [
                        'user_id' => $user->id,
                        'shop_id' => $shop->id,
                    ]);
                }
            }
        }
    }
}