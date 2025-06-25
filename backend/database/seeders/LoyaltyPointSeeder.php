<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyPoint;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class LoyaltyPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer1 = User::where('email', 'buyer1@example.com')->first();
        $buyer2 = User::where('email', 'buyer2@example.com')->first();
        $orders = Order::whereIn('buyer_id', [$buyer1->id, $buyer2->id])
                       ->where('order_status', 'paid')
                       ->get();

        if ($orders->isEmpty()) {
            Log::warning('No paid orders found for loyalty points seeding');
            return;
        }

        // Giao dịch điểm thưởng cho buyer1
        LoyaltyPoint::create([
            'user_id' => $buyer1->id,
            'points' => 100,
            'transaction_type' => 'earn',
            'order_id' => $orders->random()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LoyaltyPoint::create([
            'user_id' => $buyer1->id,
            'points' => 50,
            'transaction_type' => 'spend',
            'order_id' => null,
            'created_at' => now()->addMinutes(5),
            'updated_at' => now()->addMinutes(5),
        ]);

        LoyaltyPoint::create([
            'user_id' => $buyer1->id,
            'points' => 200,
            'transaction_type' => 'earn',
            'order_id' => $orders->random()->id,
            'created_at' => now()->addMinutes(10),
            'updated_at' => now()->addMinutes(10),
        ]);

        // Giao dịch điểm thưởng cho buyer2
        LoyaltyPoint::create([
            'user_id' => $buyer2->id,
            'points' => 150,
            'transaction_type' => 'earn',
            'order_id' => $orders->random()->id,
            'created_at' => now()->addMinutes(15),
            'updated_at' => now()->addMinutes(15),
        ]);

        LoyaltyPoint::create([
            'user_id' => $buyer2->id,
            'points' => 75,
            'transaction_type' => 'spend',
            'order_id' => null,
            'created_at' => now()->addMinutes(20),
            'updated_at' => now()->addMinutes(20),
        ]);

        LoyaltyPoint::create([
            'user_id' => $buyer2->id,
            'points' => 100,
            'transaction_type' => 'earn',
            'order_id' => $orders->random()->id,
            'created_at' => now()->addMinutes(25),
            'updated_at' => now()->addMinutes(25),
        ]);
    }
}