<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = DB::table('orders')->pluck('id')->toArray();
        $products = DB::table('products')->pluck('id')->toArray();

        if (count($orderIds) > 0 && count($products) > 0) {
            $items = [];
            foreach ($orderIds as $orderId) {
                $numItems = rand(1, 3);
                $usedVariants = []; // Track used variants to avoid duplicates in one order
                for ($i = 0; $i < $numItems; $i++) {
                    $productId = $products[array_rand($products)];
                    $variants = DB::table('product_variants')
                        ->where('product_id', $productId)
                        ->where('status', 'active')
                        ->pluck('id')
                        ->toArray();

                    if (count($variants) > 0) {
                        // Pick a variant not used in this order yet
                        $availableVariants = array_diff($variants, $usedVariants);
                        if (empty($availableVariants)) {
                            continue; // Skip if no unique variants left
                        }
                        $variantId = $availableVariants[array_rand($availableVariants)];
                        $usedVariants[] = $variantId;

                        $items[] = [
                            'order_id' => $orderId,
                            'product_id' => $productId,
                            'product_variant_id' => $variantId,
                            'quantity' => rand(1, 5),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            DB::table('order_items')->insert($items);
        }
    }
}