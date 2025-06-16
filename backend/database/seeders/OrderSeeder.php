<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $buyerIds = DB::table('users')->whereIn('email', ['buyer1@example.com', 'buyer2@example.com'])->pluck('id')->toArray();
        $sellerIds = DB::table('users')->whereIn('email', ['seller1@example.com', 'seller2@example.com'])->pluck('id')->toArray();

        if (empty($buyerIds) || empty($sellerIds)) {
            Log::warning('No buyers or sellers found in users table');
            return;
        }

        $orders = [];
        for ($i = 0; $i < 10; $i++) {
            $buyer_id = $buyerIds[array_rand($buyerIds)];
            $seller_id = $sellerIds[array_rand($sellerIds)];
            $order_status = ['pending', 'paid', 'canceled'][rand(0, 2)];
            $settled_status = $order_status === 'paid' ? 'settled' : 'unsettled';
            $settled_at = $settled_status === 'settled' ? now() : null;
            $shipping_status = ['pending', 'processing', 'shipping', 'delivered'][rand(0, 3)];

            $orders[] = [
                'buyer_id' => $buyer_id,
                'seller_id' => $seller_id,
                'settled_status' => $settled_status,
                'settled_at' => $settled_at,
                'shipping_status' => $shipping_status,
                'order_status' => $order_status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('orders')->insert($orders);
    }
}