<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [];
        for ($i = 0; $i < 10; $i++) {
            $buyer_id = rand(1, 2);
            $seller_id = rand(3, 4);
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